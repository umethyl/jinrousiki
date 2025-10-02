<?php
//-- 引数管理クラス --//
class RQ {
  const PATH = '%s/request/%s.php';
  const CLASS_PREFIX = 'Request_';
  private static $file = array();
  private static $instance = null; //Request クラス

  //Request クラスの初期化
  private function __construct($class) {
    return self::$instance = new $class();
  }

  //Request クラスのロード
  public static function Load($name = 'Request', $force = false) {
    if ($force || is_null(self::$instance)) {
      if ($name != 'Request' && self::LoadFile($name)) {
	$class = self::CLASS_PREFIX . $name;
      } else {
	$class = $name;
      }
      new self($class);
    }
  }

  //ファイルロード
  public static function LoadFile($name) {
    return LoadManager::LoadFile(self::$file, $name, self::GetPath($name));
  }

  //インスタンス取得
  public static function Get() {
    return self::$instance;
  }

  //テストデータ取得
  public static function GetTest() {
    return self::Get()->GetTest();
  }

  //インスタンス代入
  public static function Set($key, $value) {
    self::Get()->$key = $value;
  }

  //テスト村データセット
  public static function SetTestRoom($key, $value) {
    self::GetTest()->test_room[$key] = $value;
  }

  //テスト村データ初期化
  public static function InitTestRoom() {
    self::Set('TestItems', new stdClass());
    self::GetTest()->is_virtual_room = true;
  }

  //テスト村データ追加
  public static function AddTestRoom($key, $value) {
    self::GetTest()->test_room[$key] .= ' ' . $value;
  }

  //データ展開
  public static function ToArray() {
    return self::Get()->ToArray();
  }

  //ファイルパス取得
  private static function GetPath($name) {
    return sprintf(self::PATH, JINROU_INC, $name);
  }

  //デバッグ用
  public static function p($data = null, $name = null) {
    Text::p(is_null($data) ? self::Get() : self::Get()->$data, $name);
  }
}

//-- 引数解析の基底クラス --//
class Request {
  public function __get($name) {
    $this->$name = false;
    return null;
  }

  //-- 解析用 --//
  //引数解析
  public function Parse($src, $filter, array $spec_list) {
    $value_list = $this->GetSource($src);
    foreach ($spec_list as $spec) {
      $value = ArrayFilter::Get($value_list, $spec);
      if (empty($filter)) {
	$this->$spec = $value;
      } elseif (method_exists($this, $filter)) {
	$this->$spec = $this->$filter($value);
      } elseif (method_exists('Text', $filter)) {
	$this->$spec = Text::$filter($value);
      } else {
	$this->$spec = $filter($value);
      }
    }
  }

  public function ParseGet($stack) {
    $stack  = func_get_args();
    $filter = array_shift($stack);
    $this->Parse('get', $filter, $stack);
  }

  public function ParsePost($stack) {
    $stack  = func_get_args();
    $filter = array_shift($stack);
    $this->Parse('post', $filter, $stack);
  }

  public function ParseRequest($stack) {
    $stack  = func_get_args();
    $filter = array_shift($stack);
    $this->Parse('request', $filter, $stack);
  }

  public function ParseGetInt($stack) {
    $stack = func_get_args();
    $this->Parse('get', 'intval', $stack);
  }

  public function ParsePostInt($stack) {
    $stack = func_get_args();
    $this->Parse('post', 'intval', $stack);
  }

  public function ParseGetOn($stack) {
    $stack = func_get_args();
    $this->Parse('get', 'IsOn', $stack);
  }

  public function ParsePostOn($stack) {
    $stack = func_get_args();
    $this->Parse('post', 'IsOn', $stack);
  }

  public function ParsePostStr($stack) {
    $stack = func_get_args();
    $this->Parse('post', 'Escape', $stack);
  }

  public function ParseGetData($stack) {
    $stack = func_get_args();
    $this->Parse('get', null, $stack);
  }

  public function ParsePostData($stack) {
    $stack = func_get_args();
    $this->Parse('post', null, $stack);
  }

  public function ParseGetRoomNo() {
    $this->ParseGet('IsRoomNo', RequestDataGame::ID);
  }

  //-- データ展開用 --//
  //全データ展開
  public function ToArray() {
    $stack = array();
    foreach ($this as $key => $value) {
      $stack[$key] = $value;
    }
    return $stack;
  }

  //URLパラメータ展開
  public function ToURL($key, $int = false) {
    if ($int) {
      return $this->$key ? URL::GetAddInt($key, $this->$key) : '';
    } else {
      return $this->$key ? URL::GetAdd($key) : '';
    }
  }

  //-- 解析判定 --//
  //対象リクエスト変数取得
  protected function GetSource($src) {
    switch ($src) {
    case 'get':
      return $_GET;

    case 'post':
      return $_POST;

    case 'file':
      return ArrayFilter::GetList($_FILES, 'file');

    case 'server':
      return $_SERVER;

    case 'request':
      return $_REQUEST;

    default:
      return array();
    }
  }

  //存在判定
  protected function Exists($arg) {
    return ! empty($arg);
  }

  //有効判定
  protected function IsOn($arg) {
    return Switcher::IsOn($arg);
  }

  //村番号判定
  protected function IsRoomNo($arg) {
    $room_no = intval($arg);
    if ($room_no < 1) {
      HTML::OutputResult(Message::REQUEST_ERROR, Message::INVALID_ROOM . Message::COLON . $room_no);
    }
    return $room_no;
  }

  //ページセット
  protected function SetPage($arg) {
    if ($arg == 'all') return $arg;
    $int = intval($arg);
    return $int > 0 ? $int : 1;
  }

  //-- テスト用 --//
  //テストデータ取得
  public function GetTest() {
    return $this->TestItems;
  }

  //テスト村データ取得
  public function GetTestRoom() {
    return $this->TestItems->test_room;
  }

  //仮想村判定
  public function IsVirtualRoom() {
    $data = $this->GetTest();
    return isset($data->is_virtual_room) && $data->is_virtual_room;
  }
}
