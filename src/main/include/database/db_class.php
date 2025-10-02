<?php
//-- DB アクセス --//
class DB {
  public  static $ROOM = null;
  public  static $USER = null;
  public  static $SELF = null;
  private static $display     = false;
  private static $instance    = null;
  private static $statement   = null;
  private static $parameter   = null;
  private static $transaction = false;

  //データベース接続クラス生成
  /*
    $id     : DatabaseConfig::$name_list から選択
    $header : HTML ヘッダ出力情報 [true: 出力済み    / false: 未出力]
    $exit   : エラー処理          [true: exit を返す / false で終了]
  */
  private function __construct($id = null, $header = false, $exit = true) {
    self::Check($header, $exit);

    //データベース名設定
    $name = null;
    if (isset($id)) {
      $name = ArrayFilter::Get(DatabaseConfig::$name_list, is_int($id) ? $id - 1 : $id);
    }
    if (is_null($name)) $name = DatabaseConfig::NAME;

    //コンストラクタ用パラメータセット
    $host = DatabaseConfig::HOST;
    try {
      self::Initialize(sprintf('mysql:host=%s;dbname=%s', $host, $name));
    } catch (PDOException $e) {
      return self::Output($header, $exit, Text::AddHeader($host, $e->getMessage()));
    }
  }

  //接続設定確認
  public static function Check($header, $exit) {
    if (DatabaseConfig::DISABLE) {
      return self::Output($header, $exit, Message::DISABLE_DB);
    }
  }

  //PDO インスタンス初期化
  public static function Initialize($dsn) {
    $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . DatabaseConfig::ENCODE);
    $pdo = new PDO($dsn, DatabaseConfig::USER, DatabaseConfig::PASSWORD, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    self::$instance = $pdo;
    return self::$instance;
  }

  //データベース接続
  public static function Connect($id = null) {
    if (is_null(self::$instance)) new self($id);
    return isset(self::$instance);
  }

  //データベース接続 (ヘッダ出力あり)
  public static function ConnectInHeader($id = null) {
    if (is_null(self::$instance)) new self($id, true, false);
    return isset(self::$instance);
  }

  //データベース再接続
  public static function Reconnect($id = null) {
    new self($id, true);
    return isset(self::$instance);
  }

  //データベース切断
  public static function Disconnect() {
    if (empty(self::$instance)) return;
    if (self::$transaction) self::Rollback();
    self::$instance = null;
  }

  //トランザクション開始
  public static function Transaction() {
    if (self::$transaction) return true; //トランザクション中ならスキップ
    return self::$transaction = self::$instance->beginTransaction();
  }

  //ロールバック処理
  public static function Rollback() {
    self::$transaction = false; //必要なら事前にフラグ判定を行う
    return self::$instance->rollBack();
  }

  //コミット処理
  public static function Commit() {
    self::$transaction = false;
    return self::$instance->commit();
  }

  //最終 INSERT ID 取得
  public static function GetInsertID() {
    return self::$instance->lastInsertId();
  }

  //Prepare 処理
  public static function Prepare($query, $list = array()) {
    self::$statement = self::$instance->prepare($query);
    self::$parameter = $list;
  }

  //SQL 実行
  public static function Execute($quiet = false) {
    try {
      if (is_null(self::$statement)) return false;
      self::$statement->execute(self::$parameter);
      if (self::$display) { //statement 表示 (デバッグ用)
	Text::p(self::$statement);
	Text::p(self::$parameter);
      }
      return self::$statement;
    } catch (PDOException $e) {
      self::Reset();
      if ($quiet) return false;
      $error = Text::AddHeader($e->getMessage(), $e->getCode());
    }
    $backtrace = debug_backtrace(); //バックトレースを取得

    //Execute() を call した関数と位置を取得して「SQLエラー」として返す
    $trace_stack = array_shift($backtrace);
    $stack       = array($trace_stack['line'], $error);

    $trace_stack = array_shift($backtrace);
    array_unshift($stack, self::GetCaller($trace_stack));
    $str = Text::AddHeader(ArrayFilter::Concat($stack, ': '), Message::SQL_ERROR) . Text::BRLF;

    foreach ($backtrace as $trace_stack) { //呼び出し元があるなら追加で出力
      $stack = array(self::GetCaller($trace_stack), $trace_stack['line']);
      $str .= Text::AddHeader(ArrayFilter::Concat($stack, ': '), 'Caller') . Text::BRLF;
    }
    HTML::OutputResult(ServerConfig::TITLE . Message::ERROR_TITLE, $str);
  }

  //カウンタロック処理
  public static function Lock($type) {
    self::Prepare('SELECT count FROM count_limit WHERE type = ?' . self::SetLock(), array($type));
    return self::Transaction() && self::FetchBool();
  }

  //実行結果を bool で受け取る
  public static function FetchBool($quiet = false) {
    return self::Execute($quiet) !== false;
  }

  //単体の値を取得
  public static function FetchResult() {
    $stmt = self::Execute();
    self::Reset();
    return $stmt instanceOf PDOStatement && $stmt->rowCount() > 0 ? $stmt->fetchColumn() : false;
  }

  //該当するデータの行数を取得
  public static function Count() {
    $stmt = self::Execute();
    self::Reset();
    return $stmt instanceOf PDOStatement ? $stmt->rowCount() : 0;
  }

  //存在判定
  public static function Exists() {
    return self::Count() > 0;
  }

  //一次元の配列を取得
  public static function FetchColumn() {
    $stmt = self::Execute();
    self::Reset();
    return $stmt instanceOf PDOStatement ? $stmt->fetchAll(PDO::FETCH_COLUMN) : array();
  }

  //連想配列を取得
  public static function FetchAssoc($shift = false) {
    $stmt = self::Execute();
    self::Reset();
    $stack = $stmt instanceOf PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
    return $shift ? array_shift($stack) : $stack;
  }

  //オブジェクト形式の配列を取得
  public static function FetchClass($class, $shift = false) {
    $stmt = self::Execute();
    self::Reset();
    $stack = $stmt instanceOf PDOStatement ? $stmt->fetchAll(PDO::FETCH_CLASS, $class) : array();
    return $shift ? array_shift($stack) : $stack;
  }

  //データベース登録
  public static function Insert($table, $items, $values) {
    self::Prepare("INSERT INTO {$table}({$items}) VALUES({$values})");
    return self::FetchBool();
  }

  //村削除
  public static function DeleteRoom($room_no) {
    $query = 'DELETE FROM %s WHERE room_no = ?';
    foreach (self::GetTableList() as $table) {
      self::Prepare(sprintf($query, $table), array($room_no));
      if (! self::FetchBool()) return false;
    }
    return true;
  }

  //最適化
  public static function Optimize($name = null) {
    $query = is_null($name) ? ArrayFilter::ToCSV(self::GetTableList()) : $name;
    self::Prepare('OPTIMIZE TABLE ' . $query);
    return self::FetchBool() && self::Commit();
  }

  //SELECT 句生成
  public static function SetSelect($table, $column) {
    return sprintf('SELECT %s FROM %s', ArrayFilter::ToCSV(ArrayFilter::Pack($column)), $table);
  }

  //WHERE 句生成
  public static function SetWhere($data) {
    return ' WHERE ' . ArrayFilter::Concat(self::SetHolder($data), ' AND ');
  }

  //IN 句生成
  public static function SetIn(array $list) {
    return ' IN ' . Text::Quote(ArrayFilter::ToCSV($list));
  }

  //ORDER BY 句生成
  public static function SetOrder($column, $order = true) {
    $stack = array();
    $list  = is_array($column) ? $column : array($column => $order);
    foreach ($list as $column => $order) {
      $stack[] = $column . ' ' . ($order ? 'ASC' : 'DESC');
    }
    return ' ORDER BY ' . ArrayFilter::ToCSV($stack);
  }

  //FOR UPDATE 句生成
  public static function SetLock($flag = true) {
    return $flag ? ' FOR UPDATE' : '';
  }

  //LIMIT 句生成
  public static function SetLimit($from, $to) {
    return sprintf(' LIMIT %d, %d', $from, $to);
  }

  //プレースホルダ用構文生成
  public static function SetHolder($data) {
    $stack = array();
    foreach (ArrayFilter::Pack($data) as $column) {
      $stack[] = $column . ' = ?';
    }
    return $stack;
  }

  //WHERE 句追加
  public static function AddWhere($data) {
    return ' AND ' . ArrayFilter::Concat(self::SetHolder($data), ' AND ');
  }

  //IN 句用配列変換
  public static function FillIn(array $list) {
    return array_fill(0, count($list), '?');
  }

  //村情報ロード
  public static function LoadRoom($lock = false) {
    self::$ROOM = new Room(RQ::Get(), $lock);
  }

  //ユーザ情報ロード
  public static function LoadUser($lock = false) {
    self::$USER = new UserLoader(RQ::Get(), $lock);
    if (self::$ROOM->IsOff(RoomMode::LOG)) self::$USER->SetEvent();
  }

  //本人情報ロード
  public static function LoadSelf($id = null) {
    self::$SELF = is_null($id) ? self::$USER->BySession() : self::$USER->ByID($id);
  }

  //本人情報ロード (観戦者)
  public static function LoadViewer() {
    self::$SELF = new User();
  }

  //本人情報ロード (身代わり君)
  public static function LoadDummyBoy() {
    self::LoadSelf(GM::ID);
  }

  //村情報セット
  public static function SetRoom(Room $class) {
    self::$ROOM = $class;
  }

  //村情報ロード済み判定
  public static function ExistsRoom() {
    return isset(self::$ROOM);
  }

  //ユーザ情報ロード済み判定
  public static function ExistsUser() {
    return isset(self::$USER);
  }

  //statement 表示設定 (デバッグ用)
  public static function d($flag = true) {
    self::$display = $flag;
  }

  //statement リセット
  private static function Reset() {
    self::$statement = null;
    self::$parameter = null;
  }

  //村削除時の対象テーブル取得
  private static function GetTableList() {
    return array(
      'room', 'user_entry', 'player', 'vote', 'system_message',
      'talk', 'talk_beforegame', 'talk_aftergame', 'user_talk_count',
      'result_ability', 'result_dead', 'result_lastwords', 'result_vote_kill'
    );
  }

  //データベース接続エラー出力 ($header, $exit は Connect() 参照)
  private static function Output($header, $exit, $str) {
    $title = Message::DB_ERROR_CONNECT;
    $body  = Text::AddHeader($str, $title);
    if ($header) {
      HTML::OutputWarning($body);
      if ($exit) HTML::OutputFooter($exit);
      return false;
    }
    HTML::OutputResult($title, $body);
  }

  //Caller 取得
  private static function GetCaller(array $list) {
    return Text::AddHeader($list['function'] . '()', ArrayFilter::Get($list, 'class'), '::');
  }
}
