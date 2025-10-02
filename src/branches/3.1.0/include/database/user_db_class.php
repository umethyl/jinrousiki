<?php
//-- DB アクセス (User 拡張) --//
class UserDB {
  /* user_entry */
  //ユーザクラス取得
  public static function Load($user_no) {
    $query = <<<EOF
SELECT user_no AS id, uname, handle_name, sex, profile, role, icon_no, u.session_id,
  color, icon_name
FROM user_entry AS u INNER JOIN user_icon USING (icon_no)
WHERE room_no = ? AND user_no = ?
EOF;
    DB::Prepare($query, array(RQ::Get()->room_no, $user_no));
    return DB::FetchClass('User', true);
  }

  //ユーザ情報取得
  public static function Get() {
    $query = 'SELECT * FROM user_entry WHERE room_no = ? AND user_no = ?';
    DB::Prepare($query, array(RQ::Get()->room_no, RQ::Get()->user_no));
    return DB::FetchAssoc(true);
  }

  //遺言取得
  public static function GetLastWords($user_no) {
    $query = 'SELECT last_words FROM user_entry WHERE room_no = ? AND user_no = ?';
    DB::Prepare($query, array(DB::$ROOM->id, $user_no));
    return DB::FetchResult();
  }

  //キック済み判定
  public static function IsKick($uname) {
    $query = 'SELECT user_no FROM user_entry WHERE room_no = ? AND live = ? AND uname = ?';
    DB::Prepare($query, array(RQ::Get()->room_no, UserLive::KICK, $uname));
    return DB::Exists();
  }

  //重複ユーザ判定
  public static function Duplicate($uname, $handle_name) {
    $query = <<<EOF
SELECT user_no FROM user_entry WHERE room_no = ? AND live = ? AND (uname = ? OR handle_name = ?)
EOF;
    DB::Prepare($query, array(RQ::Get()->room_no, UserLive::LIVE, $uname, $handle_name));
    return DB::Exists();
  }

  //重複 HN 判定
  public static function DuplicateName($user_no, $handle_name) {
    $query = <<<EOF
SELECT user_no FROM user_entry WHERE room_no = ? AND user_no <> ? AND live = ? AND handle_name = ?
EOF;
    DB::Prepare($query, array(RQ::Get()->room_no, $user_no, UserLive::LIVE, $handle_name));
    return DB::Exists();
  }

  //重複 IP 判定
  public static function DuplicateIP() {
    $query = 'SELECT user_no FROM user_entry WHERE room_no = ? AND live = ? AND ip_address = ?';
    DB::Prepare($query, array(RQ::Get()->room_no, UserLive::LIVE, Security::GetIP()));
    return DB::Exists();
  }

  //ユーザ登録処理
  public static function Insert(array $list) {
    extract($list);
    $crypt_password = Text::Crypt($password);
    $items  = 'room_no, user_no, uname, handle_name, icon_no, sex, password, live';
    $values = "{$room_no}, {$user_no}, '{$uname}', '{$handle_name}', {$icon_no}, '{$sex}', " .
      "'{$crypt_password}', 'live'";

    if ($uname != GM::DUMMY_BOY) {
      $session_id      = Session::GetUniqID();
      $ip_address      = Security::GetIP();
      $last_load_scene = RoomScene::BEFORE;
    }

    $stack = array('profile', 'role', 'session_id', 'last_words', 'ip_address', 'last_load_scene');
    foreach ($stack as $value) {
      if (isset($$value)) {
	$items  .= ", {$value}";
	$values .= ", '{$$value}'";
      }
    }
    return DB::Insert('user_entry', $items, $values);
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
    $query = 'UPDATE user_entry SET user_no = ? WHERE room_no = ? AND uname = ?';
    DB::Prepare($query, array($id, DB::$ROOM->id, $uname));
    return DB::FetchBool();
  }

  //キック処理
  public static function Kick($id) {
    $query = 'UPDATE user_entry SET live = ?, session_id = NULL WHERE room_no = ? AND user_no = ?';
    DB::Prepare($query, array(UserLive::KICK, DB::$ROOM->id, $id));
    return DB::FetchBool();
  }

  //GM ログアウト
  public static function LogoutGM() {
    $stack = array(
      'handle_name' => Message::DUMMY_BOY,
      'password'    => Text::Crypt(ServerConfig::PASSWORD)
    );
    $set   = ArrayFilter::ToCSV(DB::SetHolder(array_keys($stack))) . ', session_id = NULL';
    $where = DB::SetWhere(array('room_no', 'user_no'));
    $query = 'UPDATE user_entry SET ' . $set . $where;
    $list  = array_merge(array_values($stack), array(DB::$ROOM->id, DB::$USER->GetDummyBoyID()));
    DB::Prepare($query, $list);
    return DB::FetchBool();
  }

  /* vote */
  //投票取得
  public static function GetVote($user_no, $type, $not_type) {
    $query = <<<EOF
SELECT type, target_no FROM vote WHERE room_no = ? AND date = ? AND vote_count = ? AND 
EOF;
    $list = array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->vote_count);
    if ($type == VoteAction::WOLF || $type == VoteAction::STEP_WOLF) {
      $query .= 'type IN (?, ?, ?)';
      array_push($list, VoteAction::WOLF, VoteAction::STEP_WOLF, VoteAction::SILENT_WOLF);
    }
    elseif ($not_type != '') {
      $query .= 'user_no = ? AND type IN (?, ?)';
      array_push($list, $user_no, $type, $not_type);
    }
    else {
      $query .= 'user_no = ? AND type = ?';
      array_push($list, $user_no, $type);
    }

    DB::Prepare($query, $list);
    return DB::FetchAssoc(true);
  }

  //処刑投票済み判定
  public static function IsVoteKill() {
    //シーン進行の仕様上、この関数をコールした時点では同日投票データは処刑しか存在しない
    $query = <<<EOF
SELECT user_no FROM vote WHERE room_no = ? AND date = ? AND vote_count = ? AND user_no = ?
EOF;
    $list = array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->vote_count, DB::$SELF->id);
    DB::Prepare($query, $list);
    return DB::Exists();
  }
}

//-- DB アクセス (UserLoader 拡張) --//
class UserLoaderDB {
  //ユーザデータ取得
  public static function Load($room_no, $lock = false) {
    $table  = 'user_entry LEFT JOIN user_icon USING (icon_no)';
    $column = array(
      'room_no', 'user_no AS id', 'uname', 'handle_name',
      'profile', 'sex', 'role', 'role_id',
      'objection', 'live', 'last_load_scene', 'icon_filename', 'color'
    );
    $select = DB::SetSelect($table, $column);
    $query  = $select . DB::SetWhere('room_no') . DB::SetOrder('id') . DB::SetLock($lock);

    DB::Prepare($query, array($room_no));
    return DB::FetchClass('User');
  }

  //ユーザデータ取得 (入村処理用)
  public static function LoadEntryUser($room_no) {
    $column = array('room_no', 'user_no AS id', 'uname', 'handle_name', 'live', 'ip_address');
    $select = DB::SetSelect('user_entry', $column);
    $query  = $select . DB::SetWhere('room_no') . DB::SetOrder('id') . DB::SetLock();

    DB::Prepare($query, array($room_no));
    return DB::FetchClass('User');
  }

  //ユーザデータ取得 (ゲーム開始前)
  public static function LoadBeforegame($room_no) {
    if ($room_no != DB::$ROOM->id) return null;

    $table = <<<EOF
user_entry AS u
LEFT JOIN user_icon USING (icon_no)
LEFT JOIN vote AS v ON
  u.room_no = v.room_no AND v.vote_count = ? AND
  u.user_no = v.user_no AND v.type = ?
EOF;
    $column = array(
      'u.room_no', 'u.user_no AS id', 'u.uname', 'handle_name',
      'profile', 'sex', 'role', 'role_id',
      'objection', 'live', 'last_load_scene', 'icon_filename', 'color',
      'v.type AS vote_type'
    );
    $select = DB::SetSelect($table, $column);

    $query = $select . DB::SetWhere('u.room_no') . DB::SetOrder('id');
    $list  = array(DB::$ROOM->vote_count, VoteAction::GAME_START, DB::$ROOM->id);

    DB::Prepare($query, $list);
    return DB::FetchClass('User');
  }

  //ユーザデータ取得 (昼 + 下界)
  public static function LoadDay($room_no) {
    if ($room_no != DB::$ROOM->id) return null;

    $table = <<<EOF
user_entry AS u
LEFT JOIN user_icon USING (icon_no)
LEFT JOIN vote AS v ON
  u.room_no = v.room_no AND v.date = ? AND v.vote_count = ? AND
  u.user_no = v.user_no AND v.type = ?
EOF;
    $column = array(
      'u.room_no', 'u.user_no AS id', 'u.uname', 'handle_name',
      'profile', 'sex', 'role', 'role_id',
      'objection', 'live', 'last_load_scene', 'icon_filename', 'color',
      'v.target_no AS target_no'
    );
    $select = DB::SetSelect($table, $column);

    $query = $select . DB::SetWhere('u.room_no') . DB::SetOrder('id');
    $list  = array(DB::$ROOM->date, DB::$ROOM->vote_count, VoteAction::VOTE_KILL, DB::$ROOM->id);

    DB::Prepare($query, $list);
    return DB::FetchClass('User');
  }

  //生存陣営カウント
  public static function CountCamp($type) {
    $query = 'SELECT user_no FROM user_entry WHERE room_no = ? AND live = ? AND user_no > ? AND ';
    $list  = array(DB::$ROOM->id, UserLive::LIVE, 0);

    switch ($type) {
    case 'human':
      $query .= '!(role LIKE ?) AND !(role LIKE ?)';
      array_push($list, '%wolf%', '%fox%');
      break;

    case 'wolf':
      $query .= 'role LIKE ?';
      $list[] = '%wolf%';
      break;

    case 'fox':
      $query .= 'role LIKE ?';
      $list[] = '%fox%';
      break;

    case 'lovers':
      $query .= 'role LIKE ?';
      $list[] = '% lovers%';
      break;

    case 'quiz':
      $query .= 'role LIKE ?';
      $list[] = '%quiz%';
      break;
    }

    DB::Prepare($query, $list);
    return DB::Count();
  }
}
