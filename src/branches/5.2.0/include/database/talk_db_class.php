<?php
//-- DB アクセス (Talk 拡張) --//
final class TalkDB {
  //発言取得
  public static function Get($heaven = false) {
    if (RQ::Fetch()->IsVirtualRoom()) {
      return RQ::GetTest()->talk;
    }

    $column = ['scene', 'location', 'uname', 'action', 'sentence', 'font_type'];
    switch (DB::$ROOM->scene) {
    case RoomScene::BEFORE:
      $table = 'talk_' . DB::$ROOM->scene;
      array_push($column, 'handle_name', 'color');
      break;

    case RoomScene::AFTER:
      $table = 'talk_' . DB::$ROOM->scene;
      break;

    default:
      $table = 'talk';
      if (DB::$ROOM->IsOn(RoomMode::LOG)) {
	$column[] = 'role_id';
      }
      break;
    }
    $query = self::GetQueryBase()->Select($column);
    $list  = [DB::$ROOM->id];

    if (true === $heaven) {
      $table  = 'talk';
      $list[] = RoomScene::HEAVEN;
    } else {
      $query->Where(['date']);
      array_push($list, DB::$ROOM->date, DB::$ROOM->scene);
    }
    $query->Table($table)->Where(['scene'])->Order(['id' => false]);

    if (false === DB::$ROOM->IsPlaying()) {
      $query->Limit(0, GameConfig::LIMIT_TALK);
    }

    DB::Prepare($query->Build(), $list);
    return DB::FetchClass('TalkParser');
  }

  //発言取得 (ログ用)
  public static function GetLog($date, $scene) {
    $query  = self::GetQueryBase()->Order(['id' => RQ::Fetch()->reverse_log]);
    $list   = [DB::$ROOM->id];
    $column = ['scene', 'location', 'uname', 'action', 'sentence', 'font_type'];
    $table  = 'talk';
    if (RQ::Fetch()->time) {
      $column[] = 'time';
    }

    switch ($scene) {
    case RoomScene::BEFORE:
      $table .= '_' . $scene;
      array_push($column, 'handle_name', 'color');
      $query->Where(['scene']);
      $list[] = $scene;
      break;

    case RoomScene::AFTER:
      $table .= '_' . $scene;
      $query->Where(['scene']);
      $list[] = $scene;
      break;

    case RoomScene::HEAVEN_ONLY:
      $query->Where(['date', 'scene', 'uname'])->WhereOr(['scene', 'uname']);
      array_push($list, $date, RoomScene::HEAVEN, GM::SYSTEM);
      break;

    default:
      $column[] = 'role_id';
      $query->Where(['date']);
      $list[] = $date;

      $stack = [RoomScene::DAY, RoomScene::NIGHT];
      if (RQ::Fetch()->heaven_talk) {
	$stack[] = RoomScene::HEAVEN;
      }
      $query->WhereIn('scene', count($stack));
      ArrayFilter::AddMerge($list, $stack);
      break;
    }
    $query->Table($table)->Select($column);

    if (DB::$ROOM->IsOn(RoomMode::PERSONAL)) { //個人結果表示モード
      $query->Where(['uname']);
      $list[] = GM::SYSTEM;
    }

    DB::Prepare($query->Build(), $list);
    return DB::FetchClass('TalkParser');
  }

  //発言取得 (直近限定)
  public static function GetRecent() {
    $query = self::GetQuery()->Select(['uname', 'sentence'])
      ->Where(['scene'])->WhereNull('location')->Order(['id' => false])->Limit(5);

    DB::Prepare($query->Build(), [DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene]);
    return DB::FetchAssoc();
  }

  //発言数取得
  public static function GetUserTalkCount($lock = false) {
    $query = self::GetQueryUserTalkCount()->Select(['date', 'talk_count'])
      ->Where(['user_no'])->Lock($lock);

    DB::Prepare($query->Build(), [DB::$ROOM->id, DB::$SELF->id]);
    return DB::FetchAssoc(true);
  }

  //発言数取得 (全ユーザ)
  public static function GetAllUserTalkCount() {
    $query = self::GetQueryUserTalkCount()->Select(['user_no', 'talk_count'])->Where(['date']);
    DB::Prepare($query->Build(), [DB::$ROOM->id, DB::$ROOM->date]);

    $result = [];
    foreach (DB::FetchAssoc() as $stack) {
      $result[$stack['user_no']] = $stack['talk_count'];
    }
    return $result;
  }

  //未投票 + 発言済みユーザ人数取得
  public static function CountNoVoteTalker() {
    $table = <<<EOF
user_entry AS u
INNER JOIN user_talk_count AS t USING (room_no, user_no)
LEFT JOIN vote AS v ON u.room_no = v.room_no AND u.user_no = v.user_no
  AND t.date = v.date AND vote_count = ?
EOF;
    $query = Query::Init()->Table($table)->Select(['u.user_no'])
      ->Where(['u.room_no', 't.date', 'live'])->WhereUpper('talk_count')
      ->WhereNull('v.room_no');
    $list  = [DB::$ROOM->vote_count, DB::$ROOM->id, DB::$ROOM->date, UserLive::LIVE, 0];

    DB::Prepare($query->Build(), $list);
    return DB::Count();
  }

  //発言数更新
  public static function UpdateUserTalkCount() {
    $query = self::GetQueryUserTalkCount()->Update()->Set(['date'])->Where(['user_no']);
    if (DB::$SELF->talk_count == 0) {
      $query->SetData('talk_count', 1);
    } else {
      $query->SetIncrement('talk_count');
    }

    DB::Prepare($query->Build(), [DB::$ROOM->date, DB::$ROOM->id, DB::$SELF->id]);
    return DB::FetchBool();
  }

  //会話経過時間取得
  public static function GetSpendTime() {
    $query = self::GetQuery()->Select(['SUM(spend_time)'])->Where(['scene']);

    DB::Prepare($query->Build(), [DB::$ROOM->id, DB::$ROOM->date, DB::$ROOM->scene]);
    return (int)DB::FetchResult();
  }

  //最終シーンの夜の発言の有無を検出
  public static function ExistsLastNight() {
    $query = self::GetQuery()->Select(['id'])->Where(['scene']);

    DB::Prepare($query->Build(), [DB::$ROOM->id, DB::$ROOM->date, RoomScene::NIGHT]);
    return DB::Exists();
  }

  //共通 Query 取得
  private static function GetQuery() {
    return self::GetQueryBase()->Table('talk')->Where(['date']);
  }

  //共通 Query 取得 (user_talk_count 用)
  private static function GetQueryUserTalkCount() {
    return self::GetQueryBase()->Table('user_talk_count');
  }

  //共通 Query Base 取得
  private static function GetQueryBase() {
    return Query::Init()->Where(['room_no']);
  }
}
