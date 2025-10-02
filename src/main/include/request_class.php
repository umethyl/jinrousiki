<?php
//-- 引数管理クラス --//
class RQ {
  public static $get = null; //Request クラス

  //Request クラスの初期化
  private function __construct($class) {
    return self::$get = new $class();
  }

  //Request クラスのロード
  static function Load($class = 'RequestBase', $force = true) {
    if ($force || is_null(self::$get)) new self($class);
  }

  //インスタンス取得
  static function Get() { return self::$get; }

  //テストデータ取得
  static function GetTest() { return self::$get->GetTest(); }

  //テスト村データ取得
  static function GetTestRoom() { return self::$get->GetTestRoom(); }

  //インスタンス代入
  static function Set($key, $value) {
    self::$get->$key = $value;
  }

  //テスト村データセット
  static function SetTestRoom($key, $value) {
    self::$get->GetTest()->test_room[$key] = $value;
  }

  //テスト村データ追加
  static function AddTestRoom($key, $value) {
    self::$get->GetTest()->test_room[$key] .= ' ' . $value;
  }

  //データ展開
  static function ToArray() { return self::$get->ToArray(); }
}

//-- 引数解析の基底クラス --//
class RequestBase {
  function __get($name) {
    $this->$name = false;
    return null;
  }

  //テストデータ取得
  public function GetTest() { return $this->TestItems; }

  //テスト村データ取得
  public function GetTestRoom() { return $this->TestItems->test_room; }

  //仮想村判定
  public function IsVirtualRoom() {
    $data = $this->GetTest();
    return isset($data->is_virtual_room) && $data->is_virtual_room;
  }

  //データ展開
  public function ToArray() {
    $stack = array();
    foreach ($this as $key => $value) $stack[$key] = $value;
    return $stack;
  }

  //引数解析
  public function Parse($items) {
    $spec_list = func_get_args();
    $processor = array_shift($spec_list);
    foreach ($spec_list as $spec) {
      list($src, $item) = explode('.', $spec);
      $value_list = $this->GetSource($src);

      if (array_key_exists($item, $value_list)) {
	$value = $value_list[$item];
      } elseif (! $this->GetDefault($item, $value)) {
	$value = null;
      }

      if (empty($processor)) {
	$this->$item = $value;
      } elseif (method_exists($this, $processor)) {
	$this->$item = $this->$processor($value);
      } elseif (method_exists('Text', $processor)) {
	$this->$item = Text::$processor($value);
      } else {
	$this->$item = $processor($value);
      }
    }
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

  //初期値設定
  protected function GetDefault($item, &$value) { return false; }

  //存在判定
  protected function Exists($arg) { return ! empty($arg); }

  //有効判定
  protected function IsOn($arg) { return $arg == 'on'; }

  //村番号判定
  protected function IsRoomNo($arg) {
    $room_no = intval($arg);
    if ($room_no < 1) HTML::OutputResult('村番号エラー', '無効な村番号です: ' . $room_no);
    return $room_no;
  }

  //ページセット
  protected function SetPage($arg) {
    if ($arg == 'all') return $arg;
    $int = intval($arg);
    return $int > 0 ? $int : 1;
  }

  //テスト用パラメータセット
  protected function AttachTestParameters() {
    if (ServerConfig::DEBUG_MODE) $this->TestItems = new RequestTestParams();
  }
}

//-- テスト用パラメータ設定クラス --//
class RequestTestParams extends RequestBase {
  function __construct() {
    $this->Parse(null, 'post.test_users', 'post.test_room', 'post.test_mode');
    $this->is_virtual_room = isset($this->test_users);
  }
}

//-- game 用共通クラス --//
class RequestBaseGame extends RequestBase {
  function __construct() {
    $this->Parse('intval', 'get.room_no', 'get.auto_reload');
    $min = min(GameConfig::$auto_reload_list);
    if ($this->auto_reload != 0 && $this->auto_reload < $min) $this->auto_reload = $min;
    $this->add_role = null;
  }
}

//-- game play 用共通クラス --//
class RequestBaseGamePlay extends RequestBaseGame {
  function __construct() {
    parent::__construct();
    $this->Parse('IsOn', 'get.play_sound', 'get.icon', 'get.name', 'get.list_down');
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
  function __construct() {
    Text::Encode();
    $this->Parse('intval', 'post.icon_no');
    $this->Parse('Escape', 'post.icon_name', 'post.appearance', 'post.category',
		 'post.author', 'post.color');
    $this->Parse('Exists', 'post.search');
  }

  protected function GetIconData() {
    $this->Parse('IsOn', 'request.sort_by_name');
    $this->Parse('Escape', 'request.appearance', 'request.category', 'request.author',
		 'request.keyword');
    $this->Parse('Exists', 'request.search');
    $this->Parse('SetPage', 'request.page');
  }
}

//-- room_manager.php --//
class RequestRoomManager extends RequestBase {
  function __construct() {
    Text::Encode();
    $this->Parse('intval', 'get.room_no');
    $this->Parse('IsOn', 'post.create_room', 'post.change_room');
  }
}

//-- login.php --//
class RequestLogin extends RequestBase {
  function __construct() {
    Text::Encode();
    $this->Parse('intval', 'get.room_no');
    $this->Parse('IsOn', 'post.login_manually');
    $this->Parse('Escape', 'post.password');
    $this->Parse('ConvertTrip', 'post.uname');
  }
}

//-- user_manager.php --//
class RequestUserManager extends RequestBaseIcon {
  function __construct() {
    Text::Encode();
    $this->Parse('IsRoomNo', 'get.room_no');
    $this->Parse('intval', 'post.icon_no', 'get.user_no');
    $this->Parse('IsOn', 'post.login_manually');
    $this->Parse('Exists', 'post.entry');
    $this->Parse('Escape', 'post.password');
    $this->Parse(null, 'post.trip', 'post.profile', 'post.sex', 'post.role');
    $this->GetIconData();
    Text::Escape($this->profile, false);
    if ($this->entry) {
      $this->Parse('ConvertTrip', 'post.uname', 'post.handle_name');
    } else {
      $this->Parse('Escape', 'post.uname', 'post.trip', 'post.handle_name');
    }
  }
}

//-- game_frame.php --//
class RequestGameFrame extends RequestBaseGamePlay {
  function __construct() {
    parent::__construct();
    $this->Parse('IsOn', 'get.dead_mode');
    $this->url = $this->GetURL(true);
  }
}

//-- game_up.php --//
class RequestGameUp extends RequestBaseGamePlay {
  function __construct() {
    parent::__construct();
    $this->Parse('IsOn', 'get.dead_mode', 'get.heaven_mode');

    $url = $this->GetURL(true);
    if ($this->dead_mode)   $url .= '&dead_mode=on';
    if ($this->heaven_mode) $url .= '&heaven_mode=on';
    $this->url = $url;
  }
}

//-- game_play.php --//
class RequestGamePlay extends RequestBaseGamePlay {
  function __construct() {
    Text::Encode();
    parent::__construct();
    $this->Parse('IsOn', 'get.dead_mode', 'get.heaven_mode', 'post.set_objection');
    $this->Parse('Escape', 'post.font_type');
    $this->Parse(null, 'post.say');
    Text::Escape($this->say, false);
    $this->last_words = $this->font_type == 'last_words';
  }
}

//-- game_log.php --//
class RequestGameLog extends RequestBase {
  function __construct() {
    $this->Parse('IsRoomNo', 'get.room_no');
    $this->Parse('intval', 'get.date', 'get.user_no');
    $this->Parse(null, 'get.scene');
    if ($this->IsInvalidScene()) HTML::OutputResult('引数エラー', '無効な引数です');
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
  function __construct() {
    parent::__construct();
    $this->Parse('intval', 'post.revote_count');
    $this->Parse('IsOn', 'post.vote');
    $this->Parse(null, 'post.target_no', 'post.situation');
    $this->AttachTestParameters(); //テスト用引数のロード

    $url = $this->GetURL();
    $this->post_url = 'game_vote.php' . $url;
    $this->back_url = '<a href="game_up.php' . $url . '">←戻る &amp; reload</a>';
  }
}

//-- old_log.php --//
class RequestOldLog extends RequestBase {
  function __construct() {
    $this->Parse('intval', 'get.db_no');
    $this->Parse('IsOn', 'get.watch');
    if ($this->is_room = isset($_GET['room_no'])) {
      $this->Parse('intval', 'get.room_no', 'get.user_no');
      $this->Parse('IsOn', 'get.reverse_log', 'get.heaven_talk', 'get.heaven_only', 'get.add_role',
		   'get.wolf_sight', 'get.personal_result', 'get.time', 'get.icon');
      $this->AttachTestParameters();
    }
    else {
      $this->Parse(null, 'get.reverse');
      $this->Parse('SetPage', 'get.page');
    }
  }
}

//-- icon_view.php --//
class RequestIconView extends RequestBaseIcon {
  function __construct() {
    $this->GetIconData();
    $this->Parse('intval', 'get.icon_no');
    $this->Parse(null, 'get.category', 'get.appearance', 'get.author');
    $this->room_no = null;
  }
}

//-- icon_edit.php --//
class RequestIconEdit extends RequestBaseIcon {
  function __construct() {
    parent::__construct();
    $this->Parse('IsOn', 'post.disable');
    $this->Parse('Escape', 'post.password');
  }
}

//-- icon_upload.php --//
class RequestIconUpload extends RequestBaseIcon {
  function __construct() {
    parent::__construct();
    $this->Parse('intval', 'file.size');
    $this->Parse(null, 'post.command', 'file.type', 'file.tmp_name');
  }
}

//-- shared_room.php --//
class RequestSharedRoom extends RequestBase {
  function __construct() { $this->Parse('intval', 'get.id'); }
}

//-- src/upload.php --//
class RequestSrcUpload extends RequestBase {
  function __construct() {
    Text::Encode();
    $this->Parse('Escape', 'post.name', 'post.caption', 'post.user', 'post.password');
    $file = new StdClass();
    foreach ($this->GetSource('file') as $key => $value) {
      $file->$key = $value;
    }
    $this->file = $file;
  }
}
