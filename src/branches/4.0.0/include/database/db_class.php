<?php
//-- DB アクセス --//
final class DB {
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
    self::Enable($header, $exit);

    //データベース名設定
    $name = null;
    if (isset($id)) {
      $name = ArrayFilter::Get(DatabaseConfig::$name_list, is_int($id) ? $id - 1 : $id);
    }
    if (is_null($name)) {
      $name = DatabaseConfig::NAME;
    }

    //コンストラクタ用パラメータセット
    $host = DatabaseConfig::HOST;
    try {
      self::Initialize(sprintf('mysql:host=%s;dbname=%s', $host, $name));
    } catch (PDOException $e) {
      return self::Output($header, $exit, Text::AddHeader($host, $e->getMessage()));
    }
  }

  //接続設定確認
  public static function Enable($header, $exit) {
    if (DatabaseConfig::DISABLE) {
      return self::Output($header, $exit, Message::DISABLE_DB);
    }
  }

  //PDO インスタンス初期化
  public static function Initialize($dsn) {
    $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . DatabaseConfig::ENCODE];
    $pdo = new PDO($dsn, DatabaseConfig::USER, DatabaseConfig::PASSWORD, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    self::$instance = $pdo;
    return self::$instance;
  }

  //データベース接続
  public static function Connect($id = null) {
    if (is_null(self::$instance)) {
      new self($id);
    }
    return isset(self::$instance);
  }

  //データベース接続 (ヘッダ出力あり)
  public static function ConnectInHeader($id = null) {
    if (is_null(self::$instance)) {
      new self($id, true, false);
    }
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
    if (self::$transaction) {
      self::Rollback();
    }
    self::$instance = null;
  }

  //トランザクション開始
  public static function Transaction() {
    if (self::$transaction) return true; //トランザクション中ならスキップ
    self::$transaction = self::$instance->beginTransaction();
    return self::$transaction;
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
  public static function Prepare($query, $list = []) {
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
      if (true === $quiet) {
	return false;
      }
      $error = Text::AddHeader($e->getMessage(), $e->getCode());
    }
    $backtrace = debug_backtrace(); //バックトレースを取得

    //Execute() を call した関数と位置を取得して「SQLエラー」として返す
    $trace_stack = array_shift($backtrace);
    $stack       = [$trace_stack['line'], $error];

    $trace_stack = array_shift($backtrace);
    array_unshift($stack, self::GetCaller($trace_stack));
    $str = Text::AddHeader(ArrayFilter::Concat($stack, ': '), Message::SQL_ERROR) . Text::BRLF;

    foreach ($backtrace as $trace_stack) { //呼び出し元があるなら追加で出力
      $stack = [self::GetCaller($trace_stack), $trace_stack['line']];
      $str .= Text::AddHeader(ArrayFilter::Concat($stack, ': '), 'Caller') . Text::BRLF;
    }
    HTML::OutputResult(ServerConfig::TITLE . Message::ERROR_TITLE, $str);
  }

  //カウンタロック処理
  public static function Lock($type) {
    $query = Query::Init()->Table('count_limit')->Select(['count'])->Where(['type'])->Lock();
    self::Prepare($query->Build(), [$type]);
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
    return $stmt instanceOf PDOStatement ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
  }

  //連想配列を取得
  public static function FetchAssoc($shift = false) {
    $stmt = self::Execute();
    self::Reset();
    $stack = $stmt instanceOf PDOStatement ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    return $shift ? array_shift($stack) : $stack;
  }

  //オブジェクト形式の配列を取得
  public static function FetchClass($class, $shift = false) {
    $stmt = self::Execute();
    self::Reset();
    $stack = $stmt instanceOf PDOStatement ? $stmt->fetchAll(PDO::FETCH_CLASS, $class) : [];
    return $shift ? array_shift($stack) : $stack;
  }

  //データベース登録
  public static function Insert($table, $items, $values) {
    self::Prepare("INSERT INTO {$table}({$items}) VALUES({$values})");
    return self::FetchBool();
  }

  //村削除
  public static function DeleteRoom($room_no) {
    $query = Query::Init()->Delete()->Where(['room_no']);
    foreach (self::GetTableList() as $table) {
      $query->Table($table);
      self::Prepare($query->Build(), [$room_no]);
      if (false === self::FetchBool()) {
	return false;
      }
    }
    return true;
  }

  //最適化
  public static function Optimize($name = null) {
    $query = is_null($name) ? ArrayFilter::ToCSV(self::GetTableList()) : $name;
    self::Prepare('OPTIMIZE TABLE ' . $query);
    return self::FetchBool() && self::Commit();
  }

  //村情報ロード
  public static function LoadRoom($lock = false) {
    self::$ROOM = new Room(RQ::Get(), $lock);
  }

  //ユーザ情報ロード
  public static function LoadUser($lock = false) {
    self::$USER = new UserLoader(RQ::Get(), $lock);
    if (self::$ROOM->IsOff(RoomMode::LOG)) {
      self::$USER->SetEvent();
    }
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
    return [
      'room', 'user_entry', 'player', 'vote', 'system_message',
      'talk', 'talk_beforegame', 'talk_aftergame', 'user_talk_count',
      'result_ability', 'result_dead', 'result_lastwords', 'result_vote_kill'
    ];
  }

  //データベース接続エラー出力 ($header, $exit は Connect() 参照)
  private static function Output($header, $exit, $str) {
    $title = Message::DB_ERROR_CONNECT;
    $body  = Text::AddHeader($str, $title);
    if (true === $header) {
      HTML::OutputWarning($body);
      if (true === $exit) {
	HTML::OutputFooter($exit);
      }
      return false;
    }
    HTML::OutputResult($title, $body);
  }

  //Caller 取得
  private static function GetCaller(array $list) {
    return Text::AddHeader($list['function'] . '()', ArrayFilter::Get($list, 'class'), '::');
  }
}

//-- クエリビルダー --//
final class Query {
  const ENABLE  = 'TRUE';
  const DISABLE = 'FALSE';
  const TIME    = 'UNIX_TIMESTAMP()';
  const NOW     = 'NOW()';

  private $query = '';
  private $table;
  private $head;
  private $list = [];
  private $column = [];
  private $into = [];
  private $into_data = [];
  private $set = [];
  private $set_data = [];
  private $where = [];
  private $where_data = [];
  private $where_upper = [];
  private $where_lower = [];
  private $where_not = [];
  private $where_null = [];
  private $where_not_null = [];
  private $where_in = [];
  private $where_not_in = [];
  private $where_bool = [];
  private $where_not_true = [];
  private $where_like = [];
  private $where_not_like = [];
  private $where_or = [];
  private $where_or_like = [];
  private $group = [];
  private $order = [];
  private $limit = [];
  private $duplicate = false;
  private $distinct = false;
  private $lock = false;

  //-- 直接生成型 --//
  //LIKE 句生成
  public static function GetLike($str) {
    return Text::Quote($str, '%', '%');
  }

  //-- オブジェクト生成型 --//
  //コンストラクタ (メソッドチェイン用)
  public static function Init() {
    return new static;
  }

  //table 登録
  public function Table($table) {
    $this->table = $table;
    return $this;
  }

  //SELECT 句登録
  public function Select(array $list = []) {
    $this->head = 'SELECT';
    return $this->Store('column', $list);
  }

  //INSERT 句登録
  public function Insert() {
    $this->head = 'INSERT';
    return $this;
  }

  //INTO 句登録
  public function Into(array $list) {
    return $this->Store('into', $list);
  }

  //INTO 句登録 (値指定)
  public function IntoData($column, $data) {
    return $this->StoreData('into', 'into_data', $column, $data);
  }

  //ON DUPLICATE KEY UPDATE 句登録
  public function Duplicate() {
    $this->duplicate = true;
    return $this;
  }

  //UPDATE 句登録
  public function Update() {
    $this->head = 'UPDATE';
    return $this;
  }

  //DELETE 句登録
  public function Delete() {
    $this->head = 'DELETE';
    return $this;
  }

  //SET 句登録
  public function Set(array $list) {
    return $this->Store('set', $list);
  }

  //SET 句登録 (値指定)
  public function SetData($column, $data) {
    return $this->StoreData('set', 'set_data', $column, $data);
  }

  //SET 句登録 (+1 指定)
  public function SetIncrement($column) {
    return $this->SetData($column, sprintf('%s + 1', $column));
  }

  //SET 句登録 (NULL 指定)
  public function SetNull($column) {
    return $this->SetData($column, 'NULL');
  }

  //WHERE 句登録
  public function Where(array $list = []) {
    return $this->Store('where', $list);
  }

  //WHERE 句登録 (値指定)
  public function WhereData($column, $data) {
    return $this->StoreData('where', 'where_data', $column, $data);
  }

  //WHERE 句登録 (>)
  public function WhereUpper($column, $data = null) {
    return $this->StoreData('where', 'where_upper', $column, $data);
  }

  //WHERE 句登録 (<)
  public function WhereLower($column, $data = null) {
    return $this->StoreData('where', 'where_lower', $column, $data);
  }

  //WHERE 句登録 (IN)
  public function WhereIn($column, $count) {
    return $this->StoreData('where', 'where_in', $column, $count);
  }

  //WHERE 句登録 (NOT IN)
  public function WhereNotIn($column, $count) {
    return $this->StoreData('where', 'where_not_in', $column, $count);
  }

  //WHERE 句登録 (NOT)
  public function WhereNot($column) {
    return $this->StoreData('where', 'where_not', $column);
  }

  //WHERE 句登録 (NULL)
  public function WhereNull($column) {
    return $this->StoreData('where', 'where_null', $column);
  }

  //WHERE 句登録 (NOT NULL)
  public function WhereNotNull($column) {
    return $this->StoreData('where', 'where_not_null', $column);
  }

  //WHERE 句登録 (BOOL)
  public function WhereBool($column, $bool) {
    return $this->StoreData('where', 'where_bool', $column, $bool);
  }

  //WHERE 句登録 (NOT TRUE)
  public function WhereNotTrue($column) {
    return $this->StoreData('where', 'where_not_true', $column);
  }

  //WHERE 句登録 (LIKE)
  public function WhereLike($column) {
    return $this->StoreData('where', 'where_like', $column);
  }

  //WHERE 句登録 (NOT LIKE)
  public function WhereNotLike($column) {
    return $this->StoreData('where', 'where_not_like', $column);
  }

  //WHERE 句登録 (OR)
  public function WhereOr(array $list) {
    $this->where_or[] = $list;
    return $this;
  }

  //WHERE 句登録 (OR LIKE)
  public function WhereOrLike(array $list) {
    $this->where_or_like[] = $list;
    return $this;
  }

  //GROUP BY 句登録
  public function Group(array $list) {
    return $this->Store('group', $list);
  }

  //ORDER BY 句登録
  public function Order(array $list) {
    return $this->Store('order', $list);
  }

  //DISTINCT 句登録
  public function Distinct() {
    $this->distinct = true;
    return $this;
  }

  //LIMIT 句登録
  public function Limit($from, $to = null) {
    $this->limit = [$from, $to];
    return $this;
  }

  //FOR UPDATE 句登録
  public function Lock($lock = true) {
    $this->lock = $lock;
    return $this;
  }

  //SQL文生成
  public function Build() {
    $this->list = [];
    $this->StoreBuild($this->BuildHead());
    $this->StoreBuild($this->BuildTable());
    $this->StoreBuild($this->BuildInto());
    $this->StoreBuild($this->BuildValues());
    $this->StoreBuild($this->BuildSet());
    $this->StoreBuild($this->BuildWhere());
    $this->StoreBuild($this->BuildGroup());
    $this->StoreBuild($this->BuildOrder());
    $this->StoreBuild($this->BuildLimit());
    $this->StoreBuild($this->BuildLock());

    $this->query = ArrayFilter::Concat($this->list);
    return $this->query;
  }

  //ヘッダー情報構築
  private function BuildHead() {
    switch ($this->head) {
    case 'SELECT':
      $head = $this->head;
      if (true === $this->distinct) {
	$head .= ' DISTINCT';
      }

      $column = $this->column;
      if (count($column) < 1) {
	$column = ['*'];
      }
      return ArrayFilter::Concat([$head, ArrayFilter::ToCSV($column)]);
      break;

    case 'INSERT':
      return $this->head . ' INTO';

    default:
      return $this->head;
    }
  }

  //table 情報構築
  private function BuildTable() {
    switch ($this->head) {
    case 'INSERT':
    case 'UPDATE':
      return $this->table;

    default:
      return ArrayFilter::Concat(['FROM', $this->table]);
    }
  }

  //INTO 句構築
  private function BuildInto() {
    switch ($this->head) {
    case 'INSERT':
      if (count($this->into) < 1) {
	return null;
      }
      return Text::Quote(ArrayFilter::ToCSV($this->into));

    default:
      return null;
    }
  }

  //VALUES 句構築
  private function BuildValues() {
    switch ($this->head) {
    case 'INSERT':
      if (count($this->into) < 1) {
	return null;
      }
      break;

    default:
      return null;
    }

    $stack = [];
    foreach ($this->into as $value) {
      $data = ArrayFilter::Get($this->into_data, $value);
      if (false === is_null($data)) {
	$stack[] = $data;
      } else {
	$stack[] = '?';
      }
    }

    $list = ['VALUES', Text::Quote(ArrayFilter::ToCSV($stack))];
    if (true === $this->duplicate) {
      $list[] = 'ON DUPLICATE KEY UPDATE';
    }
    return ArrayFilter::Concat($list);
  }

  //SET 句構築
  private function BuildSet() {
    switch ($this->head) {
    case 'INSERT':
      if (false === $this->duplicate || count($this->set) < 1) {
	return null;
      }
      break;

    case 'UPDATE':
      if (count($this->set) < 1) {
	return null;
      }
      break;

    default:
      return null;
    }

    $stack = [];
    foreach ($this->set as $value) {
      $data = ArrayFilter::Get($this->set_data, $value);
      if (false === is_null($data)) {
	$stack[] = $value . ' = ' . $data;
      } else {
	$stack[] = $value . ' = ?';
      }
    }

    $list = [];
    if ($this->head == 'UPDATE') {
      $list[] = 'SET';
    }
    $list[] = ArrayFilter::ToCSV($stack);
    return ArrayFilter::Concat($list);
  }

  //WHERE 句構築
  private function BuildWhere() {
    if (count($this->where) < 1) {
      return null;
    }

    //プロパティを退避
    $where_data     = $this->where_data;
    $where_upper    = $this->where_upper;
    $where_lower    = $this->where_lower;
    $where_not      = $this->where_not;
    $where_null     = $this->where_null;
    $where_not_null = $this->where_not_null;
    $where_in       = $this->where_in;
    $where_not_in   = $this->where_not_in;
    $where_bool     = $this->where_bool;
    $where_not_true = $this->where_not_true;
    $where_like     = $this->where_like;
    $where_not_like = $this->where_not_like;
    $where_or       = $this->where_or;
    $where_or_like  = $this->where_or_like;

    //OR の先頭グループを確保
    $or_target = array_shift($where_or);
    $or_stack  = [];

    //OR LIKE の先頭グループを確保
    $or_like_target = array_shift($where_or_like);
    $or_like_stack  = [];

    $stack = [];
    foreach ($this->where as $value) {
      $data      = ArrayFilter::Get($where_data,   $value);
      $count     = ArrayFilter::Get($where_in,     $value);
      $not_count = ArrayFilter::Get($where_not_in, $value);
      $bool      = ArrayFilter::Get($where_bool,   $value);
      $upper     = ArrayFilter::Get($where_upper,  $value);
      $lower     = ArrayFilter::Get($where_lower,  $value);
      $like_flag = false;
      if (false === is_null($data)) {
	$query = $value . ' = ' . $data;
	array_shift($where_data);
      } elseif (false === is_null($count) && $count > 0) {
	$list  = array_fill(0, $count, '?');
	$query = $value . ' IN ' . Text::Quote(ArrayFilter::ToCSV($list));
	array_shift($where_in);
      } elseif (false === is_null($not_count) && $not_count > 0) {
	$list  = array_fill(0, $not_count, '?');
	$query = $value . ' NOT IN ' . Text::Quote(ArrayFilter::ToCSV($list));
	array_shift($where_not_in);
      } elseif (false === is_null($bool)) {
	$query = $value . ' IS ' . ((true === $bool) ? self::ENABLE : self::DISABLE);
	array_shift($where_bool);
      } elseif (false === is_null($upper)) {
	$query = $value . ' > ' . $upper;
	array_shift($where_upper);
      } elseif (false === is_null($lower)) {
	$query = $value . ' < ' . $lower;
	array_shift($where_lower);
      } elseif (true === in_array($value, $where_upper)) {
	$query = $value . ' > ?';
	array_shift($where_upper);
      } elseif (true === in_array($value, $where_lower)) {
	$query = $value . ' < ?';
	array_shift($where_lower);
      } elseif (true === in_array($value, $where_not)) {
	$query = $value . ' <> ?';
	array_shift($where_not);
      } elseif (true === in_array($value, $where_not_true)) {
	$query = $value . ' IS NOT TRUE';
	array_shift($where_not_true);
      } elseif (true === in_array($value, $where_null)) {
	$query = $value . ' IS NULL';
	array_shift($where_null);
      } elseif (true === in_array($value, $where_not_null)) {
	$query = $value . ' IS NOT NULL';
	array_shift($where_not_null);
      } elseif (true === in_array($value, $where_like)) {
	$query = $value . ' LIKE ?';
	array_shift($where_like);
	$like_flag = true;
      } elseif (true === in_array($value, $where_not_like)) {
	$query = $value . ' NOT LIKE ?';
	array_shift($where_not_like);
	$like_flag = true;
      } else {
	$query = $value . ' = ?';
      }

      if (ArrayFilter::IsInclude($or_target, $value)) {
	$or_stack[] = $query;
	array_shift($or_target);
	if (count($or_target) == 0) {
	  $stack[] = Text::Quote(ArrayFilter::Concat($or_stack, ' OR '));
	  $or_target = array_shift($where_or);
	  $or_stack  = [];
	}
      } elseif (true === $like_flag && ArrayFilter::IsInclude($or_like_target, $value)) {
	$or_like_stack[] = $query;
	array_shift($or_like_target);
	if (count($or_like_target) == 0) {
	  $stack[] = Text::Quote(ArrayFilter::Concat($or_like_stack, ' OR '));
	  $or_like_target = array_shift($where_or_like);
	  $or_like_stack  = [];
	}
      } else {
	$stack[] = $query;
      }
    }

    if (count($or_stack) > 0) { //最終ループ対応
      $stack[] = Text::Quote(ArrayFilter::Concat($or_stack, ' OR '));
    }

    return ArrayFilter::Concat(['WHERE', ArrayFilter::Concat($stack, ' AND ')]);
  }

  //GROUP BY 句構築
  private function BuildGroup() {
    if (count($this->group) < 1) {
      return null;
    }
    return 'GROUP BY ' . ArrayFilter::ToCSV($this->group);
  }

  //ORDER BY 句構築
  private function BuildOrder() {
    if (count($this->order) < 1) {
      return null;
    }

    $stack = [];
    foreach ($this->order as $column => $order) {
      $stack[] = $column . ' ' . ($order ? 'ASC' : 'DESC');
    }
    return 'ORDER BY ' . ArrayFilter::ToCSV($stack);
  }

  //LIMIT 句構築
  private function BuildLimit() {
    if (count($this->limit) < 1) {
      return null;
    }

    $stack = $this->limit;
    $from  = array_shift($stack);
    $to    = array_shift($stack);
    if (true === is_null($to)) {
      return sprintf('LIMIT %d', $from);
    } else {
      return sprintf('LIMIT %d, %d', $from, $to);
    }
  }

  //FOR UPDATE 句構築
  private function BuildLock() {
    return (true === $this->lock) ? 'FOR UPDATE' : null;
  }

  //プロパティ格納
  private function Store($key, array $list) {
    if (count($this->$key) > 0) {
      ArrayFilter::AddMerge($this->$key, $list);
    } else {
      $this->$key = $list;
    }
    return $this;
  }

  //プロパティ格納 (値指定)
  private function StoreData($name, $key, $column, $value = null) {
    $this->{$name}[] = $column;
    if (true === is_null($value)) {
      $this->{$key}[] = $column;
    } else {
      $this->{$key}[$column] = $value;
    }
    return $this;
  }

  //Build 一時情報格納
  private function StoreBuild($data) {
    if (false === is_null($data)) {
      $this->list[] = $data;
    }
  }

  //デバッグ用
  public function p() {
    Text::p($this->Build());
  }
}
