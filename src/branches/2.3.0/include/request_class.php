<?php
//-- 引数管理クラス --//
class RQ {
  private static $instance = null; //Request クラス

  //Request クラスの初期化
  private function __construct($class) {
    return self::$instance = new $class();
  }

  //Request クラスのロード
  static function Load($class = 'RequestBase', $force = false) {
    if ($force || is_null(self::$instance)) new self($class);
  }

  //インスタンス取得
  static function Get() {
    return self::$instance;
  }

  //テストデータ取得
  static function GetTest() {
    return self::Get()->GetTest();
  }

  //テスト村データ取得
  static function GetTestRoom() {
    return self::Get()->GetTestRoom();
  }

  //インスタンス代入
  static function Set($key, $value) {
    self::Get()->$key = $value;
  }

  //テスト村データセット
  static function SetTestRoom($key, $value) {
    self::GetTest()->test_room[$key] = $value;
  }

  //テスト村データ初期化
  static function InitTestRoom() {
    self::Set('TestItems', new StdClass());
    self::GetTest()->is_virtual_room = true;
  }

  //テスト村データ追加
  static function AddTestRoom($key, $value) {
    self::GetTest()->test_room[$key] .= ' ' . $value;
  }

  //データ展開
  static function ToArray() {
    return self::Get()->ToArray();
  }

  //デバッグ用
  static function p($data = null, $name = null) {
    Text::p(is_null($data) ? self::Get() : self::Get()->$data, $name);
  }
}

//-- 引数解析の基底クラス --//
class RequestBase {
  public function __get($name) {
    $this->$name = false;
    return null;
  }

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

  //データ展開
  public function ToArray() {
    $stack = array();
    foreach ($this as $key => $value) {
      $stack[$key] = $value;
    }
    return $stack;
  }

  //引数解析
  public function Parse($src, $filter, array $spec_list) {
    $value_list = $this->GetSource($src);
    foreach ($spec_list as $spec) {
      $value = array_key_exists($spec, $value_list) ? $value_list[$spec] : null;
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
    $this->ParseGet('IsRoomNo', 'room_no');
  }

  //対象リクエスト変数取得
  protected function GetSource($src) {
    switch ($src) {
    case 'get':
      return $_GET;

    case 'post':
      return $_POST;

    case 'file':
      return isset($_FILES['file']) ? $_FILES['file'] : array();

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
    return $arg == 'on';
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
}

//-- game 用共通クラス --//
class RequestBaseGame extends RequestBase {
  public function __construct() {
    $this->ParseGetInt('room_no', 'auto_reload');
    $min = min(GameConfig::$auto_reload_list);
    if ($this->auto_reload != 0 && $this->auto_reload < $min) $this->auto_reload = $min;
    $this->add_role = null;
  }
}

//-- game play 用共通クラス --//
class RequestBaseGamePlay extends RequestBaseGame {
  public function __construct() {
    parent::__construct();
    $this->ParseGetOn('play_sound', 'icon', 'name', 'list_down');
  }

  protected function GetURL($auto_reload = false) {
    $url = '?room_no=' . $this->room_no;
    if ($this->auto_reload > 0 || $auto_reload) $url .= '&auto_reload=' . $this->auto_reload;
    if ($this->play_sound) $url .= '&play_sound=on';
    if ($this->icon)       $url .= '&icon=on';
    if ($this->name)       $url .= '&name=on';
    if ($this->list_down)  $url .= '&list_down=on';
    return $url;
  }
}

//-- icon 用共通クラス --//
class RequestBaseIcon extends RequestBase {
  public function __construct() {
    Text::Encode();
    $this->ParsePostInt('icon_no');
    $this->ParsePostStr('icon_name', 'appearance', 'category', 'author', 'color');
    $this->ParsePost('Exists', 'search');
  }

  protected function GetIconData() {
    $this->ParseRequest('IsOn', 'sort_by_name');
    $this->ParseRequest('Escape', 'appearance', 'category', 'author', 'keyword');
    $this->ParseRequest('Exists', 'search');
    $this->ParseRequest('SetPage', 'page');
  }
}

//-- index.php --//
class RequestIndex extends RequestBase {
  public function __construct() {
    $this->ParseGetInt('id');
  }
}

//-- room_manager.php --//
class RequestRoomManager extends RequestBase {
  public function __construct() {
    Text::Encode();
    $this->ParseGetInt('room_no');
    $this->ParsePostOn('create_room', 'change_room');
    $this->ParseGetOn('describe_room');
  }
}

//-- login.php --//
class RequestLogin extends RequestBase {
  public function __construct() {
    Text::Encode();
    $this->ParseGetInt('room_no');
    $this->ParsePostOn('login_manually');
    $this->ParsePostStr('password');
    $this->ParsePostData('trip');
    $this->ParsePost('Trip', 'uname');
  }
}

//-- user_manager.php --//
class RequestUserManager extends RequestBaseIcon {
  public function __construct() {
    Text::Encode();
    $this->ParseGetRoomNo();
    $this->ParseGetInt('user_no');
    $this->ParsePostInt('icon_no');
    $this->ParsePostOn('login_manually');
    $this->ParsePostStr('password');
    $this->ParsePostData('trip', 'profile', 'sex', 'role');
    $this->ParsePost('Exists', 'entry');
    $this->GetIconData();
    Text::Escape($this->profile, false);
    if ($this->entry) {
      $this->ParsePost('Trip', 'uname', 'handle_name');
    } else {
      $this->ParsePostStr('uname', 'trip', 'handle_name');
    }
  }
}

//-- game_frame.php --//
class RequestGameFrame extends RequestBaseGamePlay {
  public function __construct() {
    parent::__construct();
    $this->ParseGetOn('dead_mode');
    $this->url = $this->GetURL(true);
  }
}

//-- game_up.php --//
class RequestGameUp extends RequestBaseGamePlay {
  public function __construct() {
    parent::__construct();
    $this->ParseGetOn('dead_mode', 'heaven_mode');

    $url = $this->GetURL(true);
    if ($this->dead_mode)   $url .= '&dead_mode=on';
    if ($this->heaven_mode) $url .= '&heaven_mode=on';
    $this->url = $url;
  }
}

//-- game_play.php --//
class RequestGamePlay extends RequestBaseGamePlay {
  public function __construct() {
    Text::Encode();
    parent::__construct();
    $this->ParseGetOn('dead_mode', 'heaven_mode');
    $this->ParsePostOn('set_objection');
    $this->ParsePostStr('font_type');
    $this->ParsePostData('say');
    Text::Escape($this->say, false);
    $this->last_words = $this->font_type == 'last_words';
  }
}

//-- game_log.php --//
class RequestGameLog extends RequestBase {
  public function __construct() {
    $this->ParseGetRoomNo();
    $this->ParseGetInt('date', 'user_no');
    $this->ParseGetData('scene');
    if ($this->IsInvalidScene()) HTML::OutputResult(Message::REQUEST_ERROR, Message::REQUEST_ERROR);
  }

  private function IsInvalidScene() {
    switch ($this->scene) {
    case 'beforegame':
      return $this->date != 0;

    case 'day':
    case 'night':
      return $this->date < 1;

    case 'aftergame':
    case 'heaven':
      return false;

    default:
      return true;
    }
  }
}

//-- game_vote.php --//
class RequestGameVote extends RequestBaseGamePlay {
  //変数の用途
  /*
    vote         : 投票ボタンを押した or 投票ページの表示の制御用
    revote_count : 昼の再投票回数
    target_no    : 投票先の user_no (キューピッドがいるため単純に整数型にキャストしないこと)
    situation    : 投票の分類 (Kick・処刑・占い・人狼襲撃など)
  */
  public function __construct() {
    parent::__construct();
    $this->ParsePostInt('revote_count');
    $this->ParsePostOn('vote', 'add_action');
    $this->ParsePostData('target_no', 'situation');

    $url = $this->GetURL();
    $this->post_url = 'game_vote.php' . $url;
    $this->back_url = HTML::GenerateLink('game_up.php' . $url, Message::BACK . ' &amp; reload');
  }
}

//-- old_log.php --//
class RequestOldLog extends RequestBase {
  public function __construct() {
    $this->ParseGetInt('db_no', 'room_no');
    $this->ParseGetOn('watch');
    if ($this->room_no > 0) {
      $this->is_room = true;
      $this->ParseGetInt('user_no', 'scroll', 'scroll_time');
      $this->ParseGetOn('reverse_log', 'heaven_talk', 'heaven_only', 'add_role', 'time', 'icon',
			'sex', 'wolf_sight', 'personal_result', 'role_list');
    }
    else {
      $this->ParseGetData('reverse', 'name');
      $this->ParseGet('SetPage', 'page');
    }
  }
}

//-- icon_view.php --//
class RequestIconView extends RequestBaseIcon {
  public function __construct() {
    $this->GetIconData();
    $this->ParseGetInt('icon_no');
    $this->ParseGetData('category', 'appearance', 'author');
    $this->room_no = null;
  }
}

//-- icon_edit.php --//
class RequestIconEdit extends RequestBaseIcon {
  public function __construct() {
    parent::__construct();
    $this->ParsePostOn('disable');
    $this->ParsePostStr('password');
  }
}

//-- icon_upload.php --//
class RequestIconUpload extends RequestBaseIcon {
  public function __construct() {
    if (Security::CheckValue($_FILES)) die();
    parent::__construct();
    $this->Parse('file', 'intval', array('size'));
    $this->Parse('file', null, array('type', 'tmp_name'));
    $this->ParsePostData('command');
  }
}

//-- shared_room.php --//
class RequestSharedRoom extends RequestBase {
  public function __construct() {
    $this->ParseGetInt('id');
  }
}

//-- src/upload.php --//
class RequestSrcUpload extends RequestBase {
  public function __construct() {
    if (Security::CheckValue($_FILES)) die();
    Text::Encode();
    $this->ParsePostStr('name', 'caption', 'user', 'password');
    $file = new StdClass();
    foreach ($this->GetSource('file') as $key => $value) {
      $file->$key = $value;
    }
    $this->file = $file;
  }
}
