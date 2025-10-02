<?php
//-- DB アクセス (アイコン拡張) --//
class IconDB {
  //情報取得
  public static function Get($icon_no) {
    self::Prepare($icon_no, '*');
    return DB::FetchAssoc(true);
  }

  //アイコン名取得
  public static function GetName($icon_no) {
    self::Prepare($icon_no, 'icon_name');
    return DB::FetchResult();
  }

  //ファイル名取得
  public static function GetFile($icon_no) {
    self::Prepare($icon_no, 'icon_filename');
    return DB::FetchResult();
  }

  //セッション情報取得
  public static function GetSession($icon_no) {
    self::Prepare($icon_no, array('icon_filename', 'session_id'));
    return DB::FetchAssoc(true);
  }

  //次のアイコン番号取得
  public static function GetNext() {
    DB::Prepare(self::SetSelect('MAX(icon_no)'));
    return (int)DB::FetchResult() + 1;
  }

  //カテゴリ取得
  public static function GetCategory() {
    $stack = array('SELECT', 'FROM user_icon WHERE', 'IS NOT NULL GROUP BY', 'ORDER BY icon_no');
    DB::Prepare(ArrayFilter::Concat($stack, ' category '));
    return DB::FetchColumn();
  }

  //リスト取得
  public static function GetList(array $where) {
    $format  = self::SetSelect('*') . ' WHERE %s ORDER BY %s';
    $where[] = 'icon_no > 0';
    $sort    = RQ::Get()->sort_by_name ? 'icon_name, icon_no' : 'icon_no, icon_name';
    $query   = sprintf($format, ArrayFilter::Concat($where, ' AND '), $sort);
    if (RQ::Get()->page != 'all') {
      $limit = max(0, IconConfig::VIEW * (RQ::Get()->page - 1));
      $query .= DB::SetLimit($limit, IconConfig::VIEW);
    }
    DB::Prepare($query);
    return DB::FetchAssoc();
  }

  //検索
  public static function Search($type) {
    //選択状態の抽出
    $data   = RQ::Get()->search ? RQ::Get()->$type : Session::Get('icon_view', $type);
    $target = empty($data) ? array() : ArrayFilter::Pack($data);
    Session::Set('icon_view', $type, $target);
    if ($type == 'keyword') return $target;

    $format = 'SELECT DISTINCT %s FROM user_icon WHERE %s IS NOT NULL';
    DB::Prepare(sprintf($format, $type, $type));
    return DB::FetchColumn();
  }

  //抽出条件生成
  public static function GetInClause($type, array $list) {
    if (in_array('__null__', $list)) return $type . ' IS NULL';
    $stack = array();
    foreach ($list as $value) {
      $stack[] = sprintf("'%s'", Text::Escape($value));
    }
    return $type . DB::SetIn($stack);
  }

  //アイコン数取得
  public static function Count(array $where) {
    $format = self::SetSelect('icon_no') . ' WHERE %s';
    $where[] = 'icon_no > 0';
    DB::Prepare(sprintf($format, ArrayFilter::Concat($where, ' AND ')));
    return DB::Count();
  }

  //存在判定
  public static function Exists($icon_no) {
    self::Prepare($icon_no, 'icon_no');
    return DB::Exists();
  }

  //アイコン名存在判定
  public static function ExistsName($icon_name) {
    DB::Prepare(self::SetSelect('icon_no') . ' WHERE icon_name = ?', array($icon_name));
    return DB::Exists();
  }

  //アイコン名重複判定
  public static function Duplicate($icon_no, $icon_name) {
    $query = self::SetSelect('icon_no') . ' WHERE icon_no <> ? AND icon_name = ?';
    DB::Prepare($query, array($icon_no, $icon_name));
    return DB::Exists();
  }

  //有効判定
  public static function Enable($icon_no) {
    DB::Prepare(self::SetQuery('icon_no') . ' AND disable IS NOT TRUE', array($icon_no));
    return DB::Exists();
  }

  //無効判定
  public static function Disable($icon_no) {
    DB::Prepare(self::SetQuery('icon_no') . ' AND disable IS TRUE', array($icon_no));
    return DB::Exists();
  }

  //村で使用中のアイコンチェック
  public static function Using($icon_no) {
    $query = <<<EOF
SELECT icon_no FROM user_icon
INNER JOIN user_entry USING (icon_no) INNER JOIN room USING (room_no)
WHERE icon_no = ? AND status IN (?, ?, ?)
EOF;
    $list = array($icon_no, RoomStatus::WAITING, RoomStatus::CLOSING, RoomStatus::PLAYING);
    DB::Prepare($query, $list);
    return DB::Exists();
  }

  //登録数上限チェック
  public static function Over() {
    DB::Prepare(self::SetSelect('icon_no'));
    return DB::Count() >= UserIconConfig::NUMBER;
  }

  //アイコン情報更新
  public static function Update($icon_no, $data) {
    DB::Prepare(sprintf('UPDATE user_icon SET %s WHERE icon_no = ?', $data), array($icon_no));
    return DB::FetchBool();
  }

  //アイコン削除
  public static function Delete($icon_no, $file) {
    DB::Prepare('DELETE FROM user_icon WHERE icon_no = ?', array($icon_no));
    if (! DB::FetchBool()) return false; //レコード削除
    unlink(Icon::GetFile($file)); //ファイル削除
    DB::Optimize('user_icon'); //テーブル最適化 + コミット
    return true;
  }

  //セッション削除
  public static function ClearSession($icon_no) {
    return self::Update($icon_no, 'session_id = NULL');
  }

  //disable セット
  public static function SetDisable() {
    return 'disable IS NOT TRUE';
  }

  //LIKE セット
  public static function SetLike($str) {
    $stack = array();
    foreach (array('category', 'appearance', 'author', 'icon_name') as $column) {
      $stack[] = sprintf("%s LIKE '%%%s%%'", $column, $str);
    }
    return Text::Quote(ArrayFilter::Concat($stack, ' OR '));
  }

  //共通 SELECT 句生成
  private static function SetSelect($column) {
    return DB::SetSelect('user_icon', $column);
  }

  //共通 SQL 文生成
  private static function SetQuery($column) {
    return self::SetSelect($column) . DB::SetWhere('icon_no');
  }

  //Prepare 処理
  private static function Prepare($icon_no, $column) {
    DB::Prepare(self::SetQuery($column), array($icon_no));
  }
}
