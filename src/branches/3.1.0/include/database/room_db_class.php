<?php
//-- DB アクセス (Room 拡張) --//
class RoomDB {
  //-- room --//
  //基本取得
  public static function Get($column, $lock = false) {
    self::Prepare(self::SetQuery($column) . DB::SetLock($lock));
    return DB::FetchResult();
  }

  //経過時間取得
  public static function GetTime() {
    return self::Get('UNIX_TIMESTAMP() - last_update_time');
  }

  //ゲームオプション取得
  public static function GetOption() {
    self::Prepare(self::SetQuery(array('game_option', 'option_role', 'max_user')));
    return DB::FetchAssoc(true);
  }

  //超過警告メッセージ出力済み判定
  public static function IsOvertimeAlert() {
    self::Prepare(self::SetQuery('overtime_alert') . ' AND overtime_alert IS FALSE');
    return ! DB::Exists();
  }

  //最終更新時刻更新
  public static function UpdateTime() {
    if (DB::$ROOM->IsTest()) return true;

    $query = 'UPDATE room SET last_update_time = UNIX_TIMESTAMP()';
    self::Prepare($query . self::SetWhere());
    return DB::FetchBool();
  }

  //投票回数更新
  public static function UpdateVoteCount($revote = false) {
    if (DB::$ROOM->IsTest()) return true;

    $query = 'UPDATE room SET vote_count = vote_count + 1, overtime_alert = FALSE';
    if ($revote) {
      $query .= ', revote_count = revote_count + 1';
    } else {
      $query .= ', last_update_time = UNIX_TIMESTAMP()';
    }
    self::Prepare($query . self::SetWhere());
    return DB::FetchBool();
  }

  //超過警告メッセージ判定フラグ変更
  public static function UpdateOvertimeAlert($bool = false) {
    if (DB::$ROOM->IsTest()) return true;

    $format = 'UPDATE room SET overtime_alert = %s, last_update_time = UNIX_TIMESTAMP()';
    self::Prepare(sprintf($format, $bool ? 'TRUE' : 'FALSE') . self::SetWhere());
    return DB::FetchBool();
  }

  //シーン変更
  public static function UpdateScene($date = false) {
    $query = <<<EOF
UPDATE room SET scene = ?, vote_count = ?, overtime_alert = FALSE,
scene_start_time = UNIX_TIMESTAMP()
EOF;
    $list = array(DB::$ROOM->scene, 1);
    if ($date) {
      $query .= ', date = ?, revote_count = ?';
      array_push($list, DB::$ROOM->date, 0);
    }
    $list[] = DB::$ROOM->id;

    DB::Prepare($query . self::SetWhere(), $list);
    return DB::FetchBool();
  }

  //村開始
  public static function Start() {
    $query = <<<EOF
UPDATE room SET status = ?, date = ?, scene = ?, vote_count = ?,
overtime_alert = FALSE, scene_start_time = UNIX_TIMESTAMP(), start_datetime = NOW()
EOF;
    $list = array(RoomStatus::PLAYING, DB::$ROOM->date, DB::$ROOM->scene, 1, DB::$ROOM->id);
    DB::Prepare($query . self::SetWhere(), $list);
    return DB::FetchBool();
  }

  //村終了
  public static function Finish($winner) {
    $query = <<<EOF
UPDATE room SET status = ?, scene = ?, winner = ?,
scene_start_time = UNIX_TIMESTAMP(), finish_datetime = NOW()
EOF;
    $list = array(RoomStatus::FINISHED, RoomScene::AFTER, $winner, DB::$ROOM->id);
    DB::Prepare($query . self::SetWhere(), $list);
    return DB::FetchBool();
  }

  //-- player --//
  //プレイヤー情報取得
  public static function GetPlayer() {
    $query = DB::SetSelect('player', array('id', 'date', 'scene', 'user_no', 'role'));
    self::Prepare($query . self::SetWhere());

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
    $column = array('user_no', 'target_no');
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
    $select = DB::SetSelect('vote', $column);
    $where  = DB::SetWhere(array('room_no', 'date', 'scene', 'vote_count'));
    $query  = $select . $where;
    $list   = array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene, DB::$ROOM->vote_count);

    DB::Prepare($query, $list);
    return DB::FetchAssoc();
  }

  //投票リセット
  public static function ResetVote() {
    if (DB::$ROOM->IsTest()) return true;
    if (! self::UpdateVoteCount()) return false;

    //即処理されるタイプの投票イベントはリセット対象外なので投票回数をスライドさせておく
    if (! DB::$ROOM->IsDate(1)) return true;
    $query = <<<EOF
UPDATE vote SET vote_count = vote_count + 1 WHERE room_no = ? AND date = ? AND type IN (?, ?)
EOF;
    $list = array(DB::$ROOM->id, DB::$ROOM->date, VoteAction::CUPID, VoteAction::DUELIST);
    DB::Prepare($query, $list);
    return DB::FetchBool();
  }

  //投票データ削除
  public static function DeleteVote() {
    if (is_null(DB::$ROOM->id)) return true;

    $query = 'DELETE FROM vote WHERE room_no = ? AND date = ?';
    $list  = array(DB::$ROOM->id, DB::$ROOM->date);
    switch (DB::$ROOM->scene) {
    case RoomScene::DAY:
      $query .= DB::AddWhere(array('type', 'revote_count'));
      array_push($list, VoteAction::VOTE_KILL, DB::$ROOM->revote_count);
      break;

    case RoomScene::NIGHT:
      if (DB::$ROOM->IsDate(1)) {
	$query .= ' AND type NOT IN (?, ?)';
	array_push($list, VoteAction::CUPID, VoteAction::DUELIST);
      } else {
	$query .= ' AND type NOT IN (?)';
	$list[] = VoteAction::VOTE_KILL;
      }
      break;
    }

    DB::Prepare($query, $list);
    return DB::Execute() && DB::Optimize('vote');
  }

  //共通 SQL 文生成
  private static function SetQuery($column) {
    return DB::SetSelect('room', $column) . self::SetWhere();
  }

  //共通 WHERE 句生成
  private static function SetWhere() {
    return DB::SetWhere('room_no');
  }

  //Prepare 処理
  private static function Prepare($query) {
    DB::Prepare($query, array(DB::$ROOM->id));
  }
}

//-- DB アクセス (システムメッセージ系拡張) --//
class SystemMessageDB {
  //イベント情報取得
  public static function GetEvent() {
    if (DB::$ROOM->IsTest()) return DevRoom::GetEvent();

    $select = DB::SetSelect('system_message', array('type', 'message'));
    $where  = self::SetWhere() . ' AND type';
    $stack  = array(
      EventType::WEATHER, EventType::EVENT, EventType::SAME_FACE, DeadReason::BLIND_VOTE
    );
    if (DB::$ROOM->IsDay()) $stack[] = EventType::VOTE_DUEL;
    $where .= DB::SetIn(DB::FillIn($stack));

    $query = $select . $where;
    $list  = array_merge(array(DB::$ROOM->id, DB::$ROOM->date), $stack);

    DB::Prepare($query, $list);
    return DB::FetchAssoc();
  }

  //天候情報取得
  public static function GetWeather($date) {
    $query = DB::SetSelect('system_message', 'message') . self::SetWhere() . DB::AddWhere('type');
    $list  = array(DB::$ROOM->id, $date, EventType::WEATHER);

    DB::Prepare($query, $list);
    return DB::FetchResult();
  }

  //能力発動結果取得
  public static function GetAbility($date, $action, $limit) {
    $select = DB::SetSelect('result_ability', array('target', 'result'));
    $where  = self::SetWhere() . DB::AddWhere('type');
    $list   = array(DB::$ROOM->id, $date, $action);
    if ($limit) {
      $where .= DB::AddWhere('user_no');
      $list[] = DB::$SELF->id;
    }
    $query = $select . $where;

    DB::Prepare($query, $list);
    return DB::FetchAssoc();
  }

  //処刑結果取得
  public static function GetVote($date) {
    $column = array('count', 'handle_name', 'target_name', 'vote', 'poll');
    $select = DB::SetSelect('result_vote_kill', $column);
    $query  = $select . self::SetWhere() . DB::SetOrder(array('count' => true, 'id' => true));
    $list   = array(DB::$ROOM->id, $date);

    DB::Prepare($query, $list);
    return DB::FetchAssoc();
  }

  //処刑結果取得 (クイズ村 GM 専用)
  public static function GetQuizVote() {
    $select = DB::SetSelect('vote', 'target_no');
    $query  = $select . self::SetWhere() . DB::AddWhere('vote_count');
    $list   = array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->vote_count);

    DB::Prepare($query, $list);
    return DB::FetchAssoc();
  }

  //死者情報取得
  public static function GetDead($shift = false) {
    if (DB::$ROOM->IsTest()) return RQ::GetTest()->result_dead;

    $select = DB::SetSelect('result_dead', array('date', 'type', 'handle_name', 'result'));
    $list   = array(DB::$ROOM->id);
    if ($shift) {
      array_push($list, DB::$ROOM->date - 1, DB::$ROOM->scene);
    } elseif (DB::$ROOM->IsDay()) {
      array_push($list, DB::$ROOM->date - 1, RoomScene::NIGHT);
    } else {
      array_push($list, DB::$ROOM->date, RoomScene::DAY);
    }
    $query = $select . self::SetWhere() . DB::AddWhere('scene');

    DB::Prepare($query, $list);
    return DB::FetchAssoc();
  }

  //遺言取得
  public static function GetLastWords($shift = false) {
    $select = DB::SetSelect('result_lastwords', array('handle_name', 'message'));
    $query  = $select . self::SetWhere();
    $list   = array(DB::$ROOM->id, DB::$ROOM->date - ($shift ? 0 : 1));

    DB::Prepare($query, $list);
    return DB::FetchAssoc();
  }

  //共通 WHERE 句生成
  private static function SetWhere() {
    return DB::SetWhere(array('room_no', 'date'));
  }
}

//-- DB アクセス (RoomLoader 拡張) --//
class RoomLoaderDB {
  //村データ取得
  public static function Get($room_no, $lock = false) {
    $column = array(
      'name', 'comment', 'date', 'scene', 'vote_count', 'revote_count', 'scene_start_time'
    );
    $query  = DB::SetSelect('room', self::SetColumn($column)) . DB::SetWhere('room_no');

    DB::Prepare($query . DB::SetLock($lock), array($room_no));
    return DB::FetchAssoc(true);
  }

  //終了した村番地を取得
  public static function GetFinished($reverse) {
    extract(self::SetFinished());
    $order = 'GROUP BY room_no' . DB::SetOrder('room_no', ! $reverse);
    $query = sprintf('%s %s %s %s', $select, $from, $where, $order);
    if (RQ::Get()->page != 'all') {
      $view = OldLogConfig::VIEW;
      $query .= DB::SetLimit($view * (RQ::Get()->page - 1), $view);
    }

    DB::Prepare($query, $list);
    return DB::FetchColumn();
  }

  //終了した村数を取得
  public static function CountFinished() {
    extract(self::SetFinished());
    $query = sprintf('%s %s %s', $select, $from, $where);
    DB::Prepare($query, $list);
    return DB::Count();
  }

  //村クラス取得 (終了)
  public static function LoadFinished($room_no) {
    $column = array(
      'name', 'comment', 'date', 'option_role', 'max_user', 'winner',
      'establish_datetime', 'start_datetime', 'finish_datetime',
      '(SELECT COUNT(user_no) FROM user_entry AS u WHERE u.room_no = r.room_no AND u.user_no > 0)
        AS user_count'
    );
    $select = DB::SetSelect('room AS r', self::SetColumn($column));
    $where  = DB::SetWhere(array('room_no', 'status'));
    $query  = $select . $where;

    return self::LoadRoom($query, array($room_no, RoomStatus::FINISHED));
  }

  //村クラス取得 (ユーザ登録用)
  public static function LoadEntryUser($room_no) {
    $column = array('date', 'scene', 'option_role', 'max_user');
    $select = DB::SetSelect('room', self::SetColumn($column));
    $where  = DB::SetWhere('room_no');
    $query  = $select . $where . DB::SetLock();

    return self::LoadRoom($query, array($room_no));
  }

  //村クラス取得 (ユーザ登録画面用)
  public static function LoadEntryUserPage() {
    $column = array('name', 'comment', 'option_role');
    $select = DB::SetSelect('room', self::SetColumn($column));
    $where  = DB::SetWhere('room_no');
    $query  = $select . $where;

    return self::LoadRoom($query, array(RQ::Get()->room_no));
  }

  //村存在判定
  public static function Exists() {
    $query = DB::SetSelect('room', 'room_no') . DB::SetWhere('room_no');
    DB::Prepare($query, array(RQ::Get()->room_no));
    return DB::Exists();
  }

  //共通 column 生成
  private static function SetColumn(array $column) {
    $list = array('room_no AS id', 'status', 'game_option');
    ArrayFilter::Merge($list, $column);
    return $list;
  }

  //LIKE 文生成
  private static function SetLike($str) {
    return '%' . $str . '%';
  }

  //終了村取得共通SQLセット
  private static function SetFinished() {
    $select = 'SELECT room_no';
    $from   = 'FROM room';
    $where  = 'WHERE status = ?';
    $list   = array(RoomStatus::FINISHED);

    if (isset(RQ::Get()->role) || isset(RQ::Get()->name)) {
      $from  .= ' INNER JOIN user_entry USING (room_no)';
    }

    if (isset(RQ::Get()->role)) {
      $where .= ' AND role LIKE ?';
      $list[] = self::SetLike(RQ::Get()->role);
    }

    if (isset(RQ::Get()->name)) {
      $where .= ' AND (uname LIKE ? OR handle_name LIKE ?)';
      $name = self::SetLike(RQ::Get()->name);
      array_push($list, $name, $name);
    }

    if (isset(RQ::Get()->room_name)) {
      $where .= ' AND name LIKE ?';
      $list[] = self::SetLike(RQ::Get()->room_name);
    }

    if (isset(RQ::Get()->winner)) {
      $where .= DB::AddWhere('winner');
      $list[] = RQ::Get()->winner;
    }

    return array('select' => $select, 'from' => $from, 'where' => $where, 'list' => $list);
  }

  //共通村クラスロード
  private static function LoadRoom($query, array $list) {
    DB::Prepare($query, $list);
    return DB::FetchClass('Room', true);
  }
}
