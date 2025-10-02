<?php
//-- DB アクセス (アイコン拡張) --//
final class IconDB {
  //情報取得
  public static function Get($icon_no) {
    self::Prepare($icon_no, []);
    return DB::FetchAssoc(true);
  }

  //アイコン名取得
  public static function GetName($icon_no) {
    self::Prepare($icon_no, ['icon_name']);
    return DB::FetchResult();
  }

  //ファイル名取得
  public static function GetFile($icon_no) {
    self::Prepare($icon_no, ['icon_filename']);
    return DB::FetchResult();
  }

  //セッション情報取得
  public static function GetSession($icon_no) {
    self::Prepare($icon_no, ['icon_filename', 'session_id']);
    return DB::FetchAssoc(true);
  }

  //次のアイコン番号取得
  public static function GetNext() {
    $query = self::GetQuerySelect(['MAX(icon_no)']);

    DB::Prepare($query->Build());
    return (int)DB::FetchResult() + 1;
  }

  //リスト取得
  public static function GetList(Query $query, array $list) {
    if (RQ::Get()->sort_by_name) {
      $query->Order(['icon_name' => true, 'icon_no'   => true]);
    } else {
      $query->Order(['icon_no'   => true, 'icon_name' => true]);
    }

    if (RQ::Get()->page != 'all') {
      $limit = max(0, IconConfig::VIEW * (RQ::Get()->page - 1));
      $query->Limit($limit, IconConfig::VIEW);
    }

    DB::Prepare($query->Build(), $list);
    return DB::FetchAssoc();
  }

  //検索
  public static function Search($type) {
    //選択状態の抽出
    $data   = RQ::Get()->search ? RQ::Get()->$type : Session::Get('icon_view', $type);
    $target = empty($data) ? [] : ArrayFilter::Pack($data);
    Session::Set('icon_view', $type, $target);
    if ($type == 'keyword') {
      return $target;
    }

    $query = self::GetQuerySelect([$type])->Distinct()->WhereNotNull($type);

    DB::Prepare($query->Build());
    return DB::FetchColumn();
  }

  //アイコン数取得
  public static function Count(Query $query, array $list) {
    DB::Prepare($query->Build(), $list);
    return DB::Count();
  }

  //存在判定
  public static function Exists($icon_no) {
    self::Prepare($icon_no, ['icon_no']);
    return DB::Exists();
  }

  //アイコン名存在判定
  public static function ExistsName($icon_name) {
    $query = self::GetQueryIconNo()->Where(['icon_name']);

    DB::Prepare($query->Build(), [$icon_name]);
    return DB::Exists();
  }

  //アイコン名重複判定
  public static function Duplicate($icon_no, $icon_name) {
    $query = self::GetQueryIconNo()->WhereNot('icon_no')->Where(['icon_name']);

    DB::Prepare($query->Build(), [$icon_no, $icon_name]);
    return DB::Exists();
  }

  //新規登録
  public static function Insert(array $list) {
    $query = self::GetQueryBase()->Insert()->Into(array_keys($list))
      ->IntoData('regist_date', Query::NOW);

    DB::Prepare($query->Build(), array_values($list));
    return DB::Execute();
  }

  //有効判定
  public static function Enable($icon_no) {
    self::PrepareBool($icon_no, false);
    return DB::Exists();
  }

  //無効判定
  public static function Disable($icon_no) {
    self::PrepareBool($icon_no, true);
    return DB::Exists();
  }

  //村で使用中のアイコンチェック
  public static function Using($icon_no) {
    $table = 'user_icon INNER JOIN user_entry USING (icon_no) INNER JOIN room USING (room_no)';
    $list  = [RoomStatus::WAITING, RoomStatus::CLOSING, RoomStatus::PLAYING];
    $query = self::GetQueryIconNo()->Table($table)
      ->Where(['icon_no'])->WhereIn('status', count($list));
    array_unshift($list, $icon_no);

    DB::Prepare($query->Build(), $list);
    return DB::Exists();
  }

  //登録数上限チェック
  public static function Over() {
    $query = self::GetQuerySelect(['icon_no']);

    DB::Prepare($query->Build());
    return DB::Count() >= UserIconConfig::NUMBER;
  }

  //アイコン情報更新
  public static function Update(Query $query, array $list) {
    DB::Prepare($query->Build(), $list);
    return DB::FetchBool();
  }

  //アイコン削除
  public static function Delete($icon_no, $file) {
    $query = self::GetQueryBase()->Delete()->Where(['icon_no']);

    DB::Prepare($query->Build(), [$icon_no]);
    if (false === DB::FetchBool()) { //レコード削除
      return false;
    }

    unlink(Icon::GetFile($file)); //ファイル削除
    DB::Optimize('user_icon'); //テーブル最適化 + コミット
    return true;
  }

  //セッション削除
  public static function ClearSession($icon_no) {
    $query = self::GetQueryUpdate()->SetNull('session_id');

    DB::Prepare($query->Build(), [$icon_no]);
    return DB::FetchBool();
  }

  //抽出条件 Query セット
  public static function SetQueryIn(Query $query, $type, array $list) {
    if (in_array('__null__', $list)) {
      $query->WhereNull($type);
      return [];
    } else {
      $query->WhereIn($type, count($list));
      return $list;
    }
  }

  //LIKE 句セット
  public static function SetQueryLike(Query $query, $str) {
    $column_list = ['category', 'appearance', 'author', 'icon_name'];
    $stack = [];
    foreach ($column_list as $column) {
      $query->WhereLike($column);
      $stack[] = Query::GetLike($str);
    }
    $query->WhereOrLike($column_list);
    return $stack;
  }

  //共通 Query 取得 (UPDATE 用)
  public static function GetQueryUpdate() {
    return self::GetQueryBase()->Update()->Where(['icon_no']);
  }

  //共通 Query 取得
  private static function GetQuery(array $column) {
    return self::GetQuerySelect($column)->Where(['icon_no']);
  }

  //共通 Query 取得 (SELECT 用)
  private static function GetQuerySelect(array $column) {
    return self::GetQueryBase()->Select($column);
  }

  //共通 Query 取得 (SELECT icon_no 用)
  private static function GetQueryIconNo() {
    return self::GetQuerySelect(['icon_no']);
  }

  //共通 Query Base 取得
  private static function GetQueryBase() {
    return Query::Init()->Table('user_icon');
  }

  //Prepare 処理
  private static function Prepare($icon_no, array $column) {
    $query = self::GetQuery($column);

    DB::Prepare($query->Build(), [$icon_no]);
  }

  //Prepare 処理 (Bool 用)
  private static function PrepareBool($icon_no, $bool) {
    $column = 'disable';
    $query  = self::GetQueryIconNo()->Where(['icon_no']);
    if (true === $bool) {
      $query->WhereBool($column, $bool);
    } else {
      $query->WhereNull($column)->WhereBool($column, $bool)->WhereOr([$column, $column]);
    }
    DB::Prepare($query->Build(), [$icon_no]);
  }
}
