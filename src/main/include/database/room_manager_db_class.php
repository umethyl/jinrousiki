<?php
//-- DB アクセス (RoomManager 拡張) --//
final class RoomManagerDB {
  //村情報取得
  public static function Load($lock = false) {
    $column = [
      'room_no AS id', 'name', 'comment', 'date', 'scene', 'status', 'game_option',
      'option_role', 'max_user'
    ];
    $query = self::GetQueryBase()->Select($column)->Where(['room_no'])->Lock($lock);

    DB::Prepare($query->Build(), [RQ::Get()->room_no]);
    return DB::FetchClass('Room', true);
  }

  //稼働中の村取得
  public static function GetList() {
    $column = [
      'room_no AS id', 'name', 'comment', 'game_option', 'option_role', 'max_user', 'status'
    ];
    $list   = self::GetStatus();
    $query  = self::GetQueryBase()->Select($column)
      ->WhereIn('status', count($list))->Order(['room_no' => false]);

    DB::Prepare($query->Build(), $list);
    return DB::FetchAssoc();
  }

  //最終村作成時刻を取得
  public static function GetLastEstablish() {
    $query = self::GetQuery(['MAX(establish_datetime)']);

    DB::Prepare($query->Build(), self::GetStatus());
    return DB::FetchResult();
  }

  //次の村番号を取得
  public static function GetNext() {
    $query = self::GetQueryBase()->Select(['MAX(room_no)']);

    DB::Prepare($query->Build());
    return (int)DB::FetchResult() + 1;
  }

  //現在の稼動数を取得
  public static function CountActive() {
    $query =self:: GetQuery(['room_no']);

    DB::Prepare($query->Build(), self::GetStatus());
    return DB::Count();
  }

  //現在の稼動数を取得 (本人作成限定)
  public static function CountEstablish() {
    $query = self::GetQuery(['room_no'])->Where(['establisher_ip']);
    $list  = array_merge(self::GetStatus(), [Security::GetIP()]);

    DB::Prepare($query->Build(), $list);
    return DB::Count();
  }

  //ユーザ数取得
  public static function CountUser($room_no) {
    $query = Query::Init()->Table('user_entry')->Select(['user_no'])->Where(['room_no']);

    DB::Prepare($query->Build(), [$room_no]);
    return DB::Count();
  }

  //村作成
  public static function Insert($room_no, $game_option, $option_role) {
    $column = [
      'room_no', 'name', 'comment', 'max_user', 'game_option',
      'option_role', 'status', 'date', 'scene', 'vote_count', 'establisher_ip'
    ];
    $list = [
      $room_no, RQ::Get()->room_name, RQ::Get()->room_comment, RQ::Get()->max_user, $game_option,
      $option_role, RoomStatus::WAITING, 0, RoomScene::BEFORE, 1, Security::GetIP()
    ];

    $query = self::GetQueryBase()->Insert()->Into($column)
      ->IntoData('scene_start_time',   Query::TIME)
      ->IntoData('last_update_time',   Query::TIME)
      ->IntoData('establish_datetime', Query::NOW);

    DB::Prepare($query->Build(), $list);
    return DB::Execute();
  }

  //村データ UPDATE
  public static function Update(array $list) {
    $query = self::GetQueryBase()->Update()->Set(array_keys($list))->Where(['room_no']);

    DB::Prepare($query->Build(), array_merge(array_values($list), [DB::$ROOM->id]));
    return DB::Execute();
  }

  //廃村処理
  /*
    厳密な処理をするには room のロックが必要になるが、廃村処理はペナルティ的な措置であり
    パフォーマンスの観点から見ても割に合わないと評価してロックは行わない
  */
  public static function DieRoom() {
    $status_list = self::GetStatus();
    $query = self::GetQueryBase()->Update()->Set(['status', 'scene'])
      ->WhereIn('status', count($status_list))
      ->WhereLower('last_update_time', sprintf('%s - ?', Query::TIME));
    $list  = array_merge(
      [RoomStatus::FINISHED, RoomScene::AFTER], $status_list, [RoomConfig::DIE_ROOM]
    );

    DB::Prepare($query->Build(), $list);
    return DB::Execute();
  }

  //セッションクリア
  /*
    厳密な処理をするには room, user_entry のロックが必要になるが、
    仕様上、強制排除措置にあたるので敢えてロックは行わない
  */
  public static function ClearSession() {
    $query = Query::Init()->Table('user_entry AS u INNER JOIN room AS r USING (room_no)')
      ->Update()->SetNull('u.session_id')
      ->WhereNotNull('u.session_id')->Where(['r.status'])
      ->WhereLower('r.finish_datetime', 'DATE_SUB(NOW(), INTERVAL ? SECOND)')
      ->WhereNull('r.finish_datetime')
      ->WhereOr(['r.finish_datetime', 'r.finish_datetime']);

    DB::Prepare($query->Build(), [RoomStatus::FINISHED, RoomConfig::KEEP_SESSION]);
    return DB::Execute();
  }

  //基本選択条件取得
  private static function GetStatus() {
    return [RoomStatus::WAITING, RoomStatus::CLOSING, RoomStatus::PLAYING];
  }

  //共通 Query 取得
  private static function GetQuery(array $column) {
    return self::GetQueryBase()->Select($column)->WhereIn('status', count(self::GetStatus()));
  }

  //共通 Query Base 取得
  private static function GetQueryBase() {
    return Query::Init()->Table('room');
  }
}
