<?php
//-- DB アクセス (Talk 拡張) --//
class TalkDB {
  //発言取得
  public static function Get($heaven = false) {
    if (RQ::Get()->IsVirtualRoom()) return RQ::GetTest()->talk;

    $format = 'SELECT %s FROM %s WHERE room_no = ?';
    $select = 'scene, location, uname, action, sentence, font_type';
    switch (DB::$ROOM->scene) {
    case RoomScene::BEFORE:
      $table = 'talk_' . DB::$ROOM->scene;
      $select .= ', handle_name, color';
      break;

    case RoomScene::AFTER:
      $table = 'talk_' . DB::$ROOM->scene;
      break;

    default:
      $table = 'talk';
      if (DB::$ROOM->IsOn(RoomMode::LOG)) $select .= ', role_id';
      break;
    }

    if ($heaven) {
      $table = 'talk';
      $scene = RoomScene::HEAVEN;
    } else {
      $scene = DB::$ROOM->scene;
    }

    $query = sprintf($format, $select, $table);
    $list  = array(DB::$ROOM->id);
    if (! $heaven) {
      $query .= ' AND date = ?';
      $list[] = DB::$ROOM->date;
    }
    $query .= ' AND scene = ? ORDER BY id DESC';
    $list[] = $scene;

    if (! DB::$ROOM->IsPlaying()) $query .= DB::SetLimit(0, GameConfig::LIMIT_TALK);
    DB::Prepare($query, $list);
    return DB::FetchClass('TalkParser');
  }

  //発言取得 (ログ用)
  public static function GetLog($set_date, $set_scene) {
    $format = 'SELECT %s FROM %s WHERE room_no = ? AND ';
    $list   = array(DB::$ROOM->id);
    $select = 'scene, location, uname, action, sentence, font_type';
    $table  = 'talk';
    if (RQ::Get()->time) $select .= ', time';

    switch ($set_scene) {
    case RoomScene::BEFORE:
      $table  .= '_' . $set_scene;
      $select .= ', handle_name, color';
      $format .= 'scene = ?';
      $list[] = $set_scene;
      break;

    case RoomScene::AFTER:
      $table .= '_' . $set_scene;
      $format .= 'scene = ?';
      $list[] = $set_scene;
      break;

    case RoomScene::HEAVEN_ONLY:
      $format .= 'date = ? AND (scene = ? OR uname = ?)';
      array_push($list, $set_date, RoomScene::HEAVEN, GM::SYSTEM);
      break;

    default:
      $select .= ', role_id';
      $format .= 'date = ? AND scene';
      $list[] = $set_date;

      $stack = array(RoomScene::DAY, RoomScene::NIGHT);
      if (RQ::Get()->heaven_talk) $stack[] = RoomScene::HEAVEN;
      $format .= DB::SetIn(DB::FillIn($stack));
      ArrayFilter::Merge($list, $stack);
      break;
    }

    if (DB::$ROOM->IsOn(RoomMode::PERSONAL)) { //個人結果表示モード
      $format .= ' AND uname = ?';
      $list[] = GM::SYSTEM;
    }
    $query = sprintf($format, $select, $table) . DB::SetOrder('id', RQ::Get()->reverse_log);

    DB::Prepare($query, $list);
    return DB::FetchClass('TalkParser');
  }

  //発言取得 (直近限定)
  public static function GetRecent() {
    $query = <<<EOF
SELECT uname, sentence FROM talk WHERE room_no = ? AND date = ? AND scene = ? AND location IS NULL
ORDER BY id DESC LIMIT 5
EOF;
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene));
    return DB::FetchAssoc();
  }

  //発言数取得
  public static function GetUserTalkCount($lock = false) {
    $query = <<<EOF
SELECT date, talk_count FROM user_talk_count WHERE room_no = ? AND user_no = ?
EOF;
    DB::Prepare($query . DB::SetLock($lock), array(DB::$ROOM->id, DB::$SELF->id));
    return DB::FetchAssoc(true);
  }

  //発言数取得 (全ユーザ)
  public static function GetAllUserTalkCount() {
    $query = <<<EOF
SELECT user_no, talk_count FROM user_talk_count WHERE room_no = ? AND date = ?
EOF;
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date));

    $result = array();
    foreach (DB::FetchAssoc() as $stack) {
      $result[$stack['user_no']] = $stack['talk_count'];
    }
    return $result;
  }

  //未投票 + 発言済みユーザ人数取得
  public static function CountNoVoteTalker() {
    $query = <<<EOF
SELECT u.user_no FROM user_entry AS u
INNER JOIN user_talk_count AS t USING (room_no, user_no)
LEFT JOIN vote AS v ON u.room_no = v.room_no AND u.user_no = v.user_no
  AND t.date = v.date AND vote_count = ?
WHERE u.room_no = ? AND t.date = ? AND live = ? AND talk_count > 0 AND v.room_no IS NULL
EOF;
    $list = array(DB::$ROOM->vote_count, DB::$ROOM->id, DB::$ROOM->date, UserLive::LIVE);
    DB::Prepare($query, $list);
    return DB::Count();
  }

  //発言数更新
  public static function UpdateUserTalkCount() {
    $query = 'UPDATE user_talk_count SET date = ?, ';
    if (DB::$SELF->talk_count == 0) {
      $query .= 'talk_count = 1';
    } else {
      $query .= 'talk_count = talk_count + 1';
    }
    $query .= ' WHERE room_no = ? AND user_no = ?';
    DB::Prepare($query, array(DB::$ROOM->date, DB::$ROOM->id, DB::$SELF->id));
    return DB::FetchBool();
  }

  //会話経過時間取得
  public static function GetSpendTime() {
    $query = 'SELECT SUM(spend_time) FROM talk WHERE room_no = ? AND date = ? AND scene = ?';
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene));
    return (int)DB::FetchResult();
  }

  //最終シーンの夜の発言の有無を検出
  public static function ExistsLastNight() {
    $query = 'SELECT id FROM talk WHERE room_no = ? AND date = ? AND scene = ?';
    DB::Prepare($query, array(DB::$ROOM->id, DB::$ROOM->date, RoomScene::NIGHT));
    return DB::Exists();
  }
}
