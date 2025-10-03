<?php
//-- DB アクセス (User 拡張) --//
final class UserDB {
  /* user_entry */
  //ユーザクラス取得
  public static function Load($user_no) {
    $table  = 'user_entry AS u INNER JOIN user_icon USING (icon_no)';
    $column = [
      'user_no AS id', 'uname', 'handle_name', 'sex', 'profile', 'role', 'icon_no',
      'u.session_id', 'color', 'icon_name'
    ];
    $query  = self::GetQuery()->Table($table)->Select($column)->Where(['user_no']);

    DB::Prepare($query->Build(), [RQ::Get()->room_no, $user_no]);
    return DB::FetchClass('User', true);
  }

  //ユーザ情報取得
  public static function Get() {
    $query = self::GetQuery()->Select()->Where(['user_no']);

    DB::Prepare($query->Build(), [RQ::Get()->room_no, RQ::Get()->user_no]);
    return DB::FetchAssoc(true);
  }

  //遺言取得
  public static function GetLastWords($user_no) {
    $query = self::GetQuery()->Select(['last_words'])->Where(['user_no']);

    DB::Prepare($query->Build(), [DB::$ROOM->id, $user_no]);
    return DB::FetchResult();
  }

  //キック済み判定
  public static function IsKick($uname) {
    $query = self::GetQueryExists()->Where(['live', 'uname']);

    DB::Prepare($query->Build(), [RQ::Get()->room_no, UserLive::KICK, $uname]);
    return DB::Exists();
  }

  //重複ユーザ判定
  public static function Duplicate($uname, $handle_name) {
    $query = self::GetQueryExists()
      ->Where(['live', 'uname', 'handle_name'])->WhereOr(['uname', 'handle_name']);

    DB::Prepare($query->Build(), [RQ::Get()->room_no, UserLive::LIVE, $uname, $handle_name]);
    return DB::Exists();
  }

  //重複 HN 判定
  public static function DuplicateName($user_no, $handle_name) {
    $query = self::GetQueryExists()->WhereNot('user_no')->Where(['live', 'handle_name']);

    DB::Prepare($query->Build(), [RQ::Get()->room_no, $user_no, UserLive::LIVE, $handle_name]);
    return DB::Exists();
  }

  //重複 IP 判定
  public static function DuplicateIP() {
    $query = self::GetQueryExists()->Where(['live', 'ip_address']);

    DB::Prepare($query->Build(), [RQ::Get()->room_no, UserLive::LIVE, Security::GetIP()]);
    return DB::Exists();
  }

  //ユーザ登録処理
  public static function Insert(array $list) {
    $list['objection'] = 0;
    $list['live']      = UserLive::LIVE;
    $list['password']  = Text::Crypt($list['password']);
    if ($list['uname'] != GM::DUMMY_BOY) {
      $list['session_id']      = Session::GetUniqID();
      $list['ip_address']      = Security::GetIP();
      $list['last_load_scene'] = RoomScene::BEFORE;
    }
    $query = Query::Init()->Table('user_entry')->Insert()->Into(array_keys($list));

    DB::Prepare($query->Build(), array_values($list));
    return DB::Execute();
  }

  //更新処理 (汎用)
  public static function Update($set, array $list, $id) {
    $query = sprintf('UPDATE user_entry SET %s WHERE room_no = ? AND user_no = ?', $set);
    array_push($list, DB::$ROOM->id, $id);

    DB::Prepare($query, $list);
    return DB::FetchBool();
  }

  //更新処理 (ID 専用)
  public static function UpdateID($id, $uname) {
    $query =self::GetQueryUpdate()->Set(['user_no'])->Where(['uname']);

    DB::Prepare($query->Build(), [$id, DB::$ROOM->id, $uname]);
    return DB::FetchBool();
  }

  //キック処理
  public static function Kick($id) {
    $query = self::GetQueryUpdate()->Set(['live'])->SetNull('session_id')->Where(['user_no']);

    DB::Prepare($query->Build(), [UserLive::KICK, DB::$ROOM->id, $id]);
    return DB::FetchBool();
  }

  //GMログアウト
  public static function LogoutGM() {
    $query = self::GetQueryUpdate()->Set(['handle_name', 'password'])->SetNull('session_id')
      ->Where(['user_no']);
    $list  = [
      Message::DUMMY_BOY, Text::Crypt(ServerConfig::PASSWORD), DB::$ROOM->id,
      DB::$USER->GetDummyBoyID()
    ];

    DB::Prepare($query->Build(), $list);
    return DB::FetchBool();
  }

  //共通 Query 取得
  private static function GetQuery() {
    return Query::Init()->Table('user_entry')->Where(['room_no']);
  }

  //共通 Query 取得 (Exists 用)
  private static function GetQueryExists() {
    return self::GetQuery()->Select(['user_no']);
  }

  //共通 Query 取得 (Update 用)
  private static function GetQueryUpdate() {
    return self::GetQuery()->Update();
  }

  /* vote */
  //投票取得
  public static function GetVote($user_no, $type, $not_type) {
    $query = self::GetQueryVote()->Select(['type', 'target_no']);
    $list  = [DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->vote_count];

    if ($type == VoteAction::WOLF || $type == VoteAction::STEP_WOLF) {
      $query->WhereIn('type', 3);
      array_push($list, VoteAction::WOLF, VoteAction::STEP_WOLF, VoteAction::SILENT_WOLF);
    } elseif ($not_type != '') {
      $query->Where(['user_no'])->WhereIn('type', 2);
      array_push($list, $user_no, $type, $not_type);
    } else {
      $query->Where(['user_no', 'type']);
      array_push($list, $user_no, $type);
    }

    DB::Prepare($query->Build(), $list);
    return DB::FetchAssoc(true);
  }

  //処刑投票済み判定
  public static function IsVoteKill() {
    //シーン進行の仕様上、この関数をコールした時点では同日投票データは処刑しか存在しない
    $query = self::GetQueryVote()->Select(['user_no'])->Where(['user_no']);
    $list  = [DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->vote_count, DB::$SELF->id];

    DB::Prepare($query->Build(), $list);
    return DB::Exists();
  }

  //共通 Query 取得 (vote 用)
  private static function GetQueryVote() {
    return Query::Init()->Table('vote')->Where(['room_no', 'date', 'vote_count']);
  }
}

//-- DB アクセス (UserLoader 拡張) --//
final class UserLoaderDB {
  //ユーザデータ取得
  public static function Load($room_no, $lock = false) {
    $table  = 'user_entry LEFT JOIN user_icon USING (icon_no)';
    $column = [
      'room_no', 'user_no AS id', 'uname', 'handle_name',
      'profile', 'sex', 'role', 'role_id',
      'objection', 'live', 'last_load_scene', 'icon_filename', 'color'
    ];
    $query = Query::Init()->Table($table)->Select($column)
      ->Where(['room_no'])->Order(['id' => true])->Lock($lock);

    DB::Prepare($query->Build(), [$room_no]);
    return DB::FetchClass('User');
  }

  //ユーザデータ取得 (入村処理用)
  public static function LoadEntryUser($room_no) {
    $column = ['room_no', 'user_no AS id', 'uname', 'handle_name', 'live', 'ip_address'];
    $query  = Query::Init()->Table('user_entry')->Select($column)
      ->Where(['room_no'])->Order(['id' => true])->Lock();

    DB::Prepare($query->Build(), [$room_no]);
    return DB::FetchClass('User');
  }

  //ユーザデータ取得 (ゲーム開始前)
  public static function LoadBeforegame($room_no) {
    if ($room_no != DB::$ROOM->id) {
      return null;
    }

    $table = <<<EOF
user_entry AS u
LEFT JOIN user_icon USING (icon_no)
LEFT JOIN vote AS v ON
  u.room_no = v.room_no AND v.vote_count = ? AND
  u.user_no = v.user_no AND v.type = ?
EOF;
    $column = [
      'u.room_no', 'u.user_no AS id', 'u.uname', 'handle_name',
      'profile', 'sex', 'role', 'role_id',
      'objection', 'live', 'last_load_scene', 'icon_filename', 'color',
      'v.type AS vote_type'
    ];
    $query = Query::Init()->Table($table)->Select($column)
      ->Where(['u.room_no'])->Order(['id' => true]);
    $list  = [DB::$ROOM->vote_count, VoteAction::GAME_START, DB::$ROOM->id];

    DB::Prepare($query->Build(), $list);
    return DB::FetchClass('User');
  }

  //ユーザデータ取得 (昼 + 下界)
  public static function LoadDay($room_no) {
    if ($room_no != DB::$ROOM->id) {
      return null;
    }

    $table = <<<EOF
user_entry AS u
LEFT JOIN user_icon USING (icon_no)
LEFT JOIN vote AS v ON
  u.room_no = v.room_no AND v.date = ? AND v.vote_count = ? AND
  u.user_no = v.user_no AND v.type = ?
EOF;
    $column = [
      'u.room_no', 'u.user_no AS id', 'u.uname', 'handle_name',
      'profile', 'sex', 'role', 'role_id',
      'objection', 'live', 'last_load_scene', 'icon_filename', 'color',
      'v.target_no AS target_no'
    ];
    $query = Query::Init()->Table($table)->Select($column)
      ->Where(['u.room_no'])->Order(['id' => true]);
    $list  = [DB::$ROOM->date, DB::$ROOM->vote_count, VoteAction::VOTE_KILL, DB::$ROOM->id];

    DB::Prepare($query->Build(), $list);
    return DB::FetchClass('User');
  }

  //生存陣営カウント
  public static function CountCamp($camp) {
    $query = Query::Init()->Table('user_entry')->Select(['user_no'])
      ->Where(['room_no', 'live'])->WhereUpper('user_no');
    $list  = [DB::$ROOM->id, UserLive::LIVE, 0];

    switch ($camp) {
    case Camp::HUMAN:
      $query->WhereNotLike('role')->WhereNotLike('role');
      array_push($list, Query::GetLike(Camp::WOLF), Query::GetLike(Camp::FOX));
      break;

    case Camp::WOLF:
    case Camp::FOX:
    case Camp::QUIZ:
      $query->WhereLike('role');
      $list[] = Query::GetLike($camp);
      break;

    case Camp::LOVERS:
      $query->WhereLike('role');
      $list[] = Query::GetLike(' ' . $camp);
      break;
    }

    DB::Prepare($query->Build(), $list);
    return DB::Count();
  }
}
