<?php
//-- DB アクセス (Room 拡張) --//
final class RoomDB {
  //-- room --//
  //基本取得
  public static function Get($column, $lock = false) {
    self::Prepare(self::GetQuery([$column])->Lock($lock));
    return DB::FetchResult();
  }

  //経過時間取得
  public static function GetTime() {
    return self::Get(sprintf('%s - last_update_time', Query::TIME));
  }

  //ゲームオプション取得
  public static function GetOption() {
    self::Prepare(self::GetQuery(['game_option', 'option_role', 'max_user']));
    return DB::FetchAssoc(true);
  }

  //超過警告メッセージ出力済み判定
  public static function IsOvertimeAlert() {
    $column = 'overtime_alert';
    self::Prepare(self::GetQuery([$column])->WhereBool($column, false));
    return false === DB::Exists();
  }

  //最終更新時刻更新
  public static function UpdateTime() {
    if (DB::$ROOM->IsTest()) {
      return true;
    }

    $query = self::GetQueryUpdate()->SetData('last_update_time', Query::TIME);
    self::Prepare($query);
    return DB::FetchBool();
  }

  //投票回数更新
  public static function UpdateVoteCount($revote = false) {
    if (DB::$ROOM->IsTest()) {
      return true;
    }

    $query = self::GetQueryUpdate()
      ->SetIncrement('vote_count')->SetData('overtime_alert', Query::DISABLE);
    if (true === $revote) {
      $query->SetIncrement('revote_count');
    } else {
      $query->SetData('last_update_time', Query::TIME);
    }

    self::Prepare($query);
    return DB::FetchBool();
  }

  //超過警告メッセージ判定フラグ変更
  public static function UpdateOvertimeAlert($bool = false) {
    if (DB::$ROOM->IsTest()) {
      return true;
    }

    $query = self::GetQueryUpdate()
      ->SetData('overtime_alert', (true === $bool) ? Query::ENABLE : Query::DISABLE)
      ->SetData('last_update_time', Query::TIME);

    self::Prepare($query);
    return DB::FetchBool();
  }

  //シーン変更
  public static function UpdateScene($update_date = false) {
    $query = self::GetQueryUpdate()->Set(['scene', 'vote_count'])
      ->SetData('overtime_alert',   Query::DISABLE)
      ->SetData('scene_start_time', Query::TIME);
    $list  = [DB::$ROOM->scene, 1];

    if (true === $update_date) {
      $query->Set(['date', 'revote_count']);
      array_push($list, DB::$ROOM->date, 0);
    }
    $list[] = DB::$ROOM->id;

    DB::Prepare($query->Build(), $list);
    return DB::FetchBool();
  }

  //村開始
  public static function Start() {
    $query = self::GetQueryUpdate()->Set(['status', 'date', 'scene', 'vote_count'])
      ->SetData('overtime_alert',   Query::DISABLE)
      ->SetData('scene_start_time', Query::TIME)
      ->SetData('start_datetime',   Query::NOW);
    $list = [RoomStatus::PLAYING, DB::$ROOM->date, DB::$ROOM->scene, 1, DB::$ROOM->id];

    DB::Prepare($query->Build(), $list);
    return DB::FetchBool();
  }

  //村終了
  public static function Finish($winner) {
    $query = self::GetQueryUpdate()->Set(['status', 'scene', 'winner'])
      ->SetData('scene_start_time', Query::TIME)
      ->SetData('finish_datetime',  Query::NOW);
    $list = [RoomStatus::FINISHED, RoomScene::AFTER, $winner, DB::$ROOM->id];

    DB::Prepare($query->Build(), $list);
    return DB::FetchBool();
  }

  //-- player --//
  //プレイヤー情報取得
  public static function GetPlayer() {
    $query = self::GetQuery(['id', 'date', 'scene', 'user_no', 'role'])->Table('player');
    self::Prepare($query);

    $result = new stdClass();
    foreach (DB::FetchAssoc() as $stack) {
      extract($stack);
      $result->role_list[$id] = $role;
      $result->user_list[$user_no][]     = $id;
      $result->timeline[$date][$scene][] = $id;
    }
    return $result;
  }

  //-- vote --//
  //投票結果取得
  public static function GetVote() {
    $column = ['user_no', 'target_no'];
    switch (DB::$ROOM->scene) {
    case RoomScene::BEFORE:
    case RoomScene::NIGHT:
      $column[] = 'type';
      break;

    case RoomScene::DAY: //必要に応じて revote_count を WHERE に足す (不要のはず)
      $column[] = 'vote_number';
      break;

    default:
      return null;
    }

    $query = Query::Init()->Table('vote')->Select($column)
      ->Where(['room_no', 'date', 'scene', 'vote_count']);
    $list  = [DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene, DB::$ROOM->vote_count];

    DB::Prepare($query->Build(), $list);
    return DB::FetchAssoc();
  }

  //投票リセット
  public static function ResetVote() {
    if (DB::$ROOM->IsTest()) {
      return true;
    }

    //投票回数を更新して現行投票を無効扱いとする
    if (false === self::UpdateVoteCount()) {
      return false;
    }

    //即処理されるタイプの投票イベントはリセット対象外なので投票回数をスライドさせておく
    if (false === DateBorder::One()) { //即処理型は1日目のみ
      return true;
    }

    $query = Query::Init()->Table('vote')->Update()->SetIncrement('vote_count')
      ->Where(['room_no', 'date'])->WhereIn('type', 2);
    $list = [DB::$ROOM->id, DB::$ROOM->date, VoteAction::CUPID, VoteAction::DUELIST];

    DB::Prepare($query->Build(), $list);
    return DB::FetchBool();
  }

  //投票データ削除
  public static function DeleteVote() {
    if (null === DB::$ROOM->id) {
      return true;
    }

    $query = Query::Init()->Table('vote')->Delete()->Where(['room_no', 'date']);
    $list  = [DB::$ROOM->id, DB::$ROOM->date];
    switch (DB::$ROOM->scene) {
    case RoomScene::DAY:
      $query->Where(['type', 'revote_count']);
      array_push($list, VoteAction::VOTE_KILL, DB::$ROOM->revote_count);
      break;

    case RoomScene::NIGHT:
      if (DateBorder::One()) {
	$query->WhereNotIn('type', 2);
	array_push($list, VoteAction::CUPID, VoteAction::DUELIST);
      } else {
	$query->WhereNotIn('type', 1);
	$list[] = VoteAction::VOTE_KILL;
      }
      break;
    }

    DB::Prepare($query->Build(), $list);
    return DB::Execute() && DB::Optimize('vote');
  }

  //共通 Query 取得
  private static function GetQuery(array $column) {
    return self::GetQueryBase()->Select($column);
  }

  //共通 Query 取得 (UPDATE 用)
  private static function GetQueryUpdate() {
    return self::GetQueryBase()->Update();
  }

  //共通 Query Base 取得
  private static function GetQueryBase() {
    return Query::Init()->Table('room')->Where(['room_no']);
  }

  //Prepare 処理
  private static function Prepare(Query $query) {
    DB::Prepare($query->Build(), [DB::$ROOM->id]);
  }
}

//-- DB アクセス (システムメッセージ系拡張) --//
final class SystemMessageDB {
  //イベント情報取得
  public static function GetEvent() {
    if (DB::$ROOM->IsTest()) {
      return DevRoom::GetEvent();
    }

    $query = self::GetQuery()->Table('system_message')->Select(['type', 'message']);
    $stack = [
      EventType::WEATHER, EventType::EVENT, EventType::SAME_FACE, DeadReason::BLIND_VOTE
    ];
    if (DB::$ROOM->IsDay()) {
      $stack[] = EventType::VOTE_DUEL;
    }
    $query->WhereIn('type', count($stack));
    $list = array_merge([DB::$ROOM->id, DB::$ROOM->date], $stack);

    DB::Prepare($query->Build(), $list);
    return DB::FetchAssoc();
  }

  //天候情報取得
  public static function GetWeather($date) {
    $query = self::GetQuery()->Table('system_message')->Select(['message'])->Where(['type']);
    $list  = [DB::$ROOM->id, $date, EventType::WEATHER];

    DB::Prepare($query->Build(), $list);
    return DB::FetchResult();
  }

  //能力発動結果取得
  public static function GetAbility($date, $action, $limit) {
    $query = self::GetQuery()->Table('result_ability')->Select(['target', 'result'])
      ->Where(['type']);
    $list  = [DB::$ROOM->id, $date, $action];
    if (true === $limit) {
      $query->Where(['user_no']);
      $list[] = DB::$SELF->id;
    }

    DB::Prepare($query->Build(), $list);
    return DB::FetchAssoc();
  }

  //処刑結果取得
  public static function GetVote($date) {
    $query = self::GetQuery()->Table('result_vote_kill')
      ->Select(['count', 'handle_name', 'target_name', 'vote', 'poll'])
      ->Order(['count' => true, 'id' => true]);
    $list  = [DB::$ROOM->id, $date];

    DB::Prepare($query->Build(), $list);
    return DB::FetchAssoc();
  }

  //処刑結果取得 (クイズ村GM専用)
  public static function GetQuizVote() {
    $query = self::GetQuery()->Table('vote')->Select(['target_no'])->Where(['vote_count']);
    $list  = [DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->vote_count];

    DB::Prepare($query->Build(), $list);
    return DB::FetchAssoc();
  }

  //死者情報取得
  public static function GetDead($shift = false) {
    if (DB::$ROOM->IsTest()) {
      return RQ::GetTest()->result_dead;
    }

    $query = self::GetQuery()->Table('result_dead')
      ->Select(['date', 'type', 'handle_name', 'result'])->Where(['scene']);
    $list  = [DB::$ROOM->id];
    if (true === $shift) {
      array_push($list, DB::$ROOM->date - 1, DB::$ROOM->scene);
    } elseif (DB::$ROOM->IsDay()) {
      array_push($list, DB::$ROOM->date - 1, RoomScene::NIGHT);
    } else {
      array_push($list, DB::$ROOM->date, RoomScene::DAY);
    }

    DB::Prepare($query->Build(), $list);
    return DB::FetchAssoc();
  }

  //遺言取得
  public static function GetLastWords($shift = false) {
    $query = self::GetQuery()->Table('result_lastwords')->Select(['handle_name', 'message']);
    $list  = [DB::$ROOM->id, DB::$ROOM->date - (true === $shift ? 0 : 1)];

    DB::Prepare($query->Build(), $list);
    return DB::FetchAssoc();
  }

  //共通 Query 取得
  private static function GetQuery() {
    return Query::Init()->Where(['room_no', 'date']);
  }
}

//-- DB アクセス (RoomLoader 拡張) --//
final class RoomLoaderDB {
  //村データ取得
  public static function Get($room_no, $lock = false) {
    $column = [
      'name', 'comment', 'date', 'scene', 'vote_count', 'revote_count', 'scene_start_time'
    ];
    $query = self::GetQuery()->Select($column)->Lock($lock);

    DB::Prepare($query->Build(), [$room_no]);
    return DB::FetchAssoc(true);
  }

  //終了した村番地を取得
  public static function GetFinished($reverse) {
    $query = Query::Init()->Group(['room_no'])->Order(['room_no' => false === $reverse]);
    if (RQ::Get()->page != 'all') {
      $view = OldLogConfig::VIEW;
      $query->Limit($view * (RQ::Get()->page - 1), $view);
    }

    self::Prepare($query);
    return DB::FetchColumn();
  }

  //終了した村数を取得
  public static function CountFinished() {
    self::Prepare(Query::Init());
    return DB::Count();
  }

  //村クラス取得 (終了)
  public static function LoadFinished($room_no) {
    $column = [
      'name', 'comment', 'date', 'option_role', 'max_user', 'winner',
      'establish_datetime', 'start_datetime', 'finish_datetime'
    ];
    $user_count_query = Query::Init()->Table('user_entry AS u')->Select(['COUNT(user_no)'])
      ->WhereData('u.room_no', 'r.room_no')->WhereUpper('u.user_no');
    $column[] = Text::Quote($user_count_query->Build()) . ' AS user_count';

    $query = self::GetQuery()->Table('room AS r')->Select($column)->Where(['status']);
    return self::LoadRoom($query, [0, $room_no, RoomStatus::FINISHED]);
  }

  //村クラス取得 (ユーザ登録用)
  public static function LoadEntryUser($room_no) {
    $query = self::GetQuery()->Select(['date', 'scene', 'option_role', 'max_user'])->Lock();
    return self::LoadRoom($query, [$room_no]);
  }

  //村クラス取得 (ユーザ登録画面用)
  public static function LoadEntryUserPage() {
    $query = self::GetQuery()->Select(['name', 'comment', 'option_role']);
    return self::LoadRoom($query, [RQ::Get()->room_no]);
  }

  //村存在判定
  public static function Exists() {
    $query = self::GetQueryBase()->Select(['room_no']);

    DB::Prepare($query->Build(), [RQ::Get()->room_no]);
    return DB::Exists();
  }

  //共通 Query 取得
  private static function GetQuery() {
    return self::GetQueryBase()->Select(['room_no AS id', 'status', 'game_option']);
  }

  //共通 Query Base 取得
  private static function GetQueryBase() {
    return Query::Init()->Table('room')->Where(['room_no']);
  }

  //Prepare 処理
  private static function Prepare(Query $query) {
    $table = 'room';
    if (isset(RQ::Get()->role) || isset(RQ::Get()->name)) {
      $table .= ' INNER JOIN user_entry USING (room_no)';
    }
    $query->Table($table)->Select(['room_no'])->Where(['status']);
    $list = [RoomStatus::FINISHED];

    if (isset(RQ::Get()->room_name)) {
      $query->WhereLike('name');
      $list[] = Query::GetLike(RQ::Get()->room_name);
    }

    if (isset(RQ::Get()->role)) {
      $query->WhereLike('role');
      $list[] = Query::GetLike(RQ::Get()->role);
    }

    if (isset(RQ::Get()->name)) {
      $query->WhereLike('uname')->WhereLike('handle_name')->WhereOr(['uname', 'handle_name']);
      $name = Query::GetLike(RQ::Get()->name);
      array_push($list, $name, $name);
    }

    if (isset(RQ::Get()->winner)) {
      $query->Where(['winner']);
      $list[] = RQ::Get()->winner;
    }

    if (isset(RQ::Get()->game_type)) {
      switch (RQ::Get()->game_type) {
      case 'normal':
	foreach (['chaos', 'duel', 'gray_random', 'quiz'] as $type) {
	  $query->WhereNotLike('game_option');
	  $list[] = Query::GetLike($type);
	}
	break;

      case 'chaos':
      case 'duel':
      case 'gray_random':
      case 'quiz':
	$query->WhereLike('game_option');
	$list[] = Query::GetLike(RQ::Get()->game_type);
	break;
      }
    }

    DB::Prepare($query->Build(), $list);
  }

  //共通村クラスロード
  private static function LoadRoom(Query $query, array $list) {
    DB::Prepare($query->Build(), $list);
    return DB::FetchClass('Room', true);
  }
}

//-- DB アクセス (RoomTalk 拡張) --//
final class RoomTalkDB {
  //発言登録
  public static function Insert(TalkStruct $talk) {
    $stack = $talk->GetStruct();
    $query = Query::Init()->Insert()->IntoData('time', Query::TIME);
    $list  = ['room_no' => DB::$ROOM->id, 'date' => DB::$ROOM->date];
    switch ($stack[TalkStruct::SCENE]) {
    case RoomScene::BEFORE:
    case RoomScene::AFTER:
      $query->Table('talk_' . $stack[TalkStruct::SCENE]);
      break;

    default:
      $query->Table('talk');
      break;
    }

    $struct_list = [
      TalkStruct::SCENE,
      TalkStruct::UNAME,
      TalkStruct::SENTENCE,
      TalkStruct::SPEND_TIME
    ];
    foreach ($struct_list as $struct) {
      $list[$struct] = $stack[$struct];
    }

    $add_struct_list = [
      TalkStruct::ACTION,
      TalkStruct::LOCATION,
      TalkStruct::FONT_TYPE,
      TalkStruct::ROLE_ID
    ];
    foreach ($add_struct_list as $struct) {
      if (isset($stack[$struct])) {
	$list[$struct] = $stack[$struct];
      }
    }
    $query->Into(array_keys($list));

    DB::Prepare($query->Build(), array_values($list));
    return DB::FetchBool();
  }

  //発言登録 (ゲーム開始前専用)
  public static function InsertBeforeGame(RoomTalkBeforeGameStruct $talk) {
    $stack = $talk->GetStruct();
    $query = Query::Init()->Table('talk_' . DB::$ROOM->scene)
      ->Insert()->IntoData('time', Query::TIME);
    $list  = ['room_no' => DB::$ROOM->id, 'date' => 0, 'scene' => DB::$ROOM->scene];

    $struct_list = [
      RoomTalkBeforeGameStruct::UNAME,
      RoomTalkBeforeGameStruct::HANDLE_NAME,
      RoomTalkBeforeGameStruct::COLOR,
      RoomTalkBeforeGameStruct::SENTENCE
    ];
    foreach ($struct_list as $struct) {
      $list[$struct] = $stack[$struct];
    }

    $add_struct_list = [RoomTalkBeforeGameStruct::FONT_TYPE];
    foreach ($add_struct_list as $struct) {
      if (isset($stack[$struct])) {
	$list[$struct] = $stack[$struct];
      }
    }
    $query->Into(array_keys($list));

    DB::Prepare($query->Build(), array_values($list));
    return DB::FetchBool();
  }
}
