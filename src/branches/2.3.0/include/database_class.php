<?php
//-- データベースアクセス --//
class DB {
  public  static $ROOM = null;
  public  static $USER = null;
  public  static $SELF = null;
  public  static $display     = false;
  private static $instance    = null;
  private static $statement   = null;
  private static $parameter   = null;
  private static $transaction = false;
  private static $table_list  = array(
    'room', 'user_entry', 'player', 'talk', 'talk_beforegame', 'talk_aftergame', 'system_message',
    'result_ability', 'result_dead', 'result_lastwords', 'result_vote_kill', 'vote');

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
      $offset = is_int($id) ? $id - 1 : $id;
      if (isset(DatabaseConfig::$name_list[$offset])) {
	$name = DatabaseConfig::$name_list[$offset];
      }
    }
    if (is_null($name)) $name = DatabaseConfig::NAME;

    //コンストラクタ用パラメータセット
    $host = DatabaseConfig::HOST;
    try {
      self::Initialize(sprintf('mysql:host=%s;dbname=%s', $host, $name));
    }
    catch (PDOException $e) {
      return self::Output($header, $exit, $e->getMessage() . ': ' . $host);
    }
  }

  //接続設定確認
  static function Check($header, $exit) {
    if (DatabaseConfig::DISABLE) {
      return self::Output($header, $exit, Message::DISABLE_DB);
    }
  }

  //PDO インスタンス初期化
  static function Initialize($dsn) {
    $pdo = new PDO($dsn, DatabaseConfig::USER, DatabaseConfig::PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    self::$instance = $pdo;
    self::Execute('SET NAMES ' . DatabaseConfig::ENCODE);
    return self::$instance;
  }

  //データベース接続
  static function Connect($id = null) {
    if (is_null(self::$instance)) new self($id);
    return isset(self::$instance);
  }

  //データベース接続 (ヘッダ出力あり)
  static function ConnectInHeader($id = null) {
    if (is_null(self::$instance)) new self($id, true, false);
    return isset(self::$instance);
  }

  //データベース再接続
  static function Reconnect($id = null) {
    new self($id, true);
    return isset(self::$instance);
  }

  //データベース切断
  static function Disconnect() {
    if (empty(self::$instance)) return;
    if (self::$transaction) self::Rollback();
    self::$instance = null;
  }

  //トランザクション開始
  static function Transaction() {
    if (self::$transaction) return true; //トランザクション中ならスキップ
    return self::$transaction = self::$instance->beginTransaction();
  }

  //カウンタロック処理
  static function Lock($type) {
    DB::Prepare('SELECT count FROM count_limit WHERE type = ? FOR UPDATE', array($type));
    return self::Transaction() && self::FetchBool();
  }

  //ロールバック処理
  static function Rollback() {
    self::$transaction = false; //必要なら事前にフラグ判定を行う
    return self::$instance->rollBack();
  }

  //コミット処理
  static function Commit() {
    self::$transaction = false;
    return self::$instance->commit();
  }

  //最終 INSERT ID 取得
  static function GetInsertID() {
    return self::$instance->lastInsertId();
  }

  //Prepare 処理
  static function Prepare($query, $list = array()) {
    self::$statement = self::$instance->prepare($query);
    self::$parameter = $list;
  }

  //SQL 実行
  static function Execute($query = null, $quiet = false) {
    try {
      if (isset($query)) {
	return self::$instance->query($query);
      }
      elseif (isset(self::$statement)) {
	self::$statement->execute(self::$parameter);
	if (self::$display) { //statement 表示 (デバッグ用)
	  Text::p(self::$statement);
	  Text::p(self::$parameter);
	}
	return self::$statement;
      }
      else {
	return false;
      }
    }
    catch (PDOException $e) {
      self::Reset();
      if ($quiet) return false;
      $error = sprintf('%d: %s', $e->getCode(), $e->getMessage());
    }
    $backtrace = debug_backtrace(); //バックトレースを取得

    //Execute() を call した関数と位置を取得して「SQLエラー」として返す
    $trace_stack = array_shift($backtrace);
    $stack       = array($trace_stack['line'], $error, $query);
    $trace_stack = array_shift($backtrace);
    array_unshift($stack, $trace_stack['function'] . '()');
    $str = sprintf('%s: %s' . Text::BRLF, Message::SQL_ERROR, implode(': ', $stack));

    foreach ($backtrace as $trace_stack) { //呼び出し元があるなら追加で出力
      $stack = array($trace_stack['function'] . '()', $trace_stack['line']);
      $str .= sprintf('Caller: %s' . Text::BRLF, implode(': ', $stack));
    }
    HTML::OutputResult(ServerConfig::TITLE . Message::ERROR_TITLE, $str);
  }

  //コミット付き実行
  static function ExecuteCommit($query = null) {
    return self::FetchBool($query) && self::Commit();
  }

  //実行結果を bool で受け取る
  static function FetchBool($query = null, $quiet = false) {
    return self::Execute($query, $quiet) !== false;
  }

  //単体の値を取得
  static function FetchResult($query = null) {
    $stmt = self::Execute($query);
    self::Reset();
    return $stmt instanceOf PDOStatement && $stmt->rowCount() > 0 ? $stmt->fetchColumn() : false;
  }

  //該当するデータの行数を取得
  static function Count($query = null) {
    $stmt = self::Execute($query);
    self::Reset();
    return $stmt instanceOf PDOStatement ? $stmt->rowCount() : 0;
  }

  //一次元の配列を取得
  static function FetchColumn($query = null) {
    $stmt = self::Execute($query);
    self::Reset();
    return $stmt instanceOf PDOStatement ? $stmt->fetchAll(PDO::FETCH_COLUMN) : array();
  }

  //連想配列を取得
  static function FetchAssoc($shift = false) {
    $stmt = self::Execute();
    self::Reset();
    $stack = $stmt instanceOf PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
    return $shift ? array_shift($stack) : $stack;
  }

  //連想配列を取得 (互換用)
  static function FetchArray($query, $shift = false) {
    $stmt = self::Execute($query);
    self::Reset();
    $stack = $stmt instanceOf PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : array();
    return $shift ? array_shift($stack) : $stack;
  }

  //オブジェクト形式の配列を取得
  static function FetchClass($class, $shift = false) {
    $stmt = self::Execute();
    self::Reset();
    $stack = $stmt instanceOf PDOStatement ? $stmt->fetchAll(PDO::FETCH_CLASS, $class) : array();
    return $shift ? array_shift($stack) : $stack;
  }

  //データベース登録
  static function Insert($table, $items, $values) {
    return self::FetchBool("INSERT INTO {$table}({$items}) VALUES({$values})");
  }

  //村削除
  static function DeleteRoom($room_no) {
    $query = 'DELETE FROM %s WHERE room_no = %d';
    foreach (self::$table_list as $table) {
      if (! self::FetchBool(sprintf($query, $table, $room_no))) return false;
    }
    return true;
  }

  //最適化
  static function Optimize($name = null) {
    $query = is_null($name) ? implode(',', self::$table_list) : $name;
    return self::ExecuteCommit('OPTIMIZE TABLE ' . $query);
  }

  //村情報ロード
  static function LoadRoom($lock = false) {
    self::$ROOM = new Room(RQ::Get(), $lock);
  }

  //ユーザ情報ロード
  static function LoadUser($lock = false) {
    self::$USER = new UserData(RQ::Get(), $lock);
  }

  //本人情報ロード
  static function LoadSelf($id = null) {
    self::$SELF = is_null($id) ? DB::$USER->BySession() : DB::$USER->ByID($id);
  }

  //本人情報ロード (観戦者)
  static function LoadViewer() {
    self::$SELF = new User();
  }

  //本人情報ロード (身代わり君)
  static function LoadDummyBoy() {
    self::LoadSelf(1);
  }

  //村情報セット
  static function SetRoom(Room $class) {
    self::$ROOM = $class;
  }

  //statement 表示設定 (デバッグ用)
  static function d($flag = true) {
    self::$display = $flag;
  }

  //statement リセット
  private static function Reset() {
    self::$statement = null;
    self::$parameter = null;
  }

  //データベース接続エラー出力 ($header, $exit は Connect() 参照)
  private static function Output($header, $exit, $str) {
    $title = Message::DB_ERROR_CONNECT;
    $body  = $title . ': ' . $str;
    if ($header) {
      Text::d(sprintf('<font color="#FF0000">%s</font>', $body));
      if ($exit) HTML::OutputFooter($exit);
      return false;
    }
    HTML::OutputResult($title, $body);
  }
}
