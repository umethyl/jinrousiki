<?php
//-- DB アクセス (RoomManager 拡張) --//
class RoomManagerDB {
  //村情報取得
  public static function Load($lock = false) {
    $query = <<<EOF
SELECT room_no AS id, name, comment, date, scene, status, game_option, option_role, max_user
FROM room WHERE room_no = ?
EOF;
    DB::Prepare($query . DB::SetLock($lock), array(RQ::Get()->room_no));
    return DB::FetchClass('Room', true);
  }

  //稼働中の村取得
  public static function GetList() {
    $query = <<<EOF
SELECT room_no AS id, name, comment, game_option, option_role, max_user, status
FROM room WHERE status IN (?, ?, ?) ORDER BY room_no DESC
EOF;
    DB::Prepare($query, self::SetStatus());
    return DB::FetchAssoc();
  }

  //最終村作成時刻を取得
  public static function GetLastEstablish() {
    DB::Prepare(self::SetQuery('MAX(establish_datetime)'), self::SetStatus());
    return DB::FetchResult();
  }

  //次の村番号を取得
  public static function GetNext() {
    DB::Prepare(self::SetSelect('MAX(room_no)'));
    return (int)DB::FetchResult() + 1;
  }

  //現在の稼動数を取得
  public static function CountActive() {
    DB::Prepare(self::SetQuery('room_no'), self::SetStatus());
    return DB::Count();
  }

  //現在の稼動数を取得 (本人作成限定)
  public static function CountEstablish() {
    $list = array_merge(self::SetStatus(), array(Security::GetIP()));
    DB::Prepare(self::SetQuery('room_no') . ' AND establisher_ip = ?', $list);
    return DB::Count();
  }

  //ユーザ数取得
  public static function CountUser($room_no) {
    DB::Prepare('SELECT user_no FROM user_entry WHERE room_no = ?', array($room_no));
    return DB::Count();
  }

  //村作成
  public static function Insert($room_no, $game_option, $option_role) {
    $query = <<<EOF
INSERT INTO room (room_no, name, comment, max_user, game_option, option_role, status, date, scene,
vote_count, scene_start_time, last_update_time, establisher_ip, establish_datetime)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ?, NOW())
EOF;
    $list = array(
      $room_no, RQ::Get()->room_name, RQ::Get()->room_comment, RQ::Get()->max_user, $game_option,
      $option_role, RoomStatus::WAITING, 0, RoomScene::BEFORE, 1, Security::GetIP()
    );
    DB::Prepare($query, $list);
    return DB::Execute();
  }

  //村データ UPDATE
  public static function Update(array $list) {
    $query  = 'UPDATE room SET %s WHERE room_no = ?';
    $update = array();
    foreach ($list as $key => $value) {
      $update[] = sprintf("%s = '%s'", $key, $value);
    }
    DB::Prepare(sprintf($query, ArrayFilter::ToCSV($update)), array(DB::$ROOM->id));
    return DB::Execute();
  }

  //廃村処理
  /*
    厳密な処理をするには room のロックが必要になるが、廃村処理はペナルティ的な措置であり
    パフォーマンスの観点から見ても割に合わないと評価してロックは行わない
  */
  public static function DieRoom() {
    $query = <<<EOF
UPDATE room SET status = ?, scene = ?
WHERE status IN (?, ?, ?) AND last_update_time < UNIX_TIMESTAMP() - ?
EOF;
    $list = array(
      RoomStatus::FINISHED, RoomScene::AFTER,
      RoomStatus::WAITING, RoomStatus::CLOSING, RoomStatus::PLAYING,
      RoomConfig::DIE_ROOM
    );
    DB::Prepare($query, $list);
    return DB::Execute();
  }

  //セッションクリア
  /*
    厳密な処理をするには room, user_entry のロックが必要になるが、
    仕様上、強制排除措置にあたるので敢えてロックは行わない
  */
  public static function ClearSession() {
    $query = <<<EOF
UPDATE user_entry AS u INNER JOIN room AS r USING (room_no)
SET u.session_id = NULL
WHERE u.session_id IS NOT NULL AND r.status = ? AND
  (r.finish_datetime IS NULL OR r.finish_datetime < DATE_SUB(NOW(), INTERVAL ? SECOND))
EOF;
    DB::Prepare($query, array(RoomStatus::FINISHED, RoomConfig::KEEP_SESSION));
    return DB::Execute();
  }

  //共通 SELECT 句生成
  private static function SetSelect($column) {
    return DB::SetSelect('room', $column);
  }

  //共通 SQL 文生成
  private static function SetQuery($column) {
    return self::SetSelect($column) . ' WHERE status IN (?, ?, ?)';
  }

  //基本選択条件セット
  private static function SetStatus() {
    return array(RoomStatus::WAITING, RoomStatus::CLOSING, RoomStatus::PLAYING);
  }
}
