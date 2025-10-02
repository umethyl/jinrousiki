<?php
//error_reporting(E_ALL);
//-- 定数を定義 --//
/*
  ServerConfig->site_root を使って CSS や画像等をロードする仕様にすると
  ローカルに保存する場合や、ログを別のサーバに移す場合に手間がかかるので
  JINRO_ROOT で相対パスを定義して共通で使用する仕様に変更しました。
  絶対パスが返る dirname() を使ったパスの定義を行わないで下さい。
*/
if(! defined('JINRO_ROOT')) define('JINRO_ROOT', '.');
define('JINRO_CONF', JINRO_ROOT . '/config');
define('JINRO_INC',  JINRO_ROOT . '/include');
define('JINRO_CSS',  JINRO_ROOT . '/css');
define('JINRO_IMG',  JINRO_ROOT . '/img');
define('JINRO_MOD',  JINRO_ROOT . '/module');

//-- クラスを定義 --//
/*
  初期化の読み込みを最適化するのが目的なので、依存情報に
  確実に読み込まれているデータを入れる必要はない。
  逆にコード上必須ではないが常にセットで使われるデータを入れると良い。
*/
class InitializeConfig{
  public $path; //パス情報格納変数
  public $loaded; //ロード情報格納変数

  //依存ファイル情報 (読み込むデータ => 依存するファイル)
  public $depend_file = array(
    'DB_CONF'             => 'server_config', //常時ロードされる
    'SERVER_CONF'         => 'server_config', //常時ロードされる
    'ROOM_CONF'           => 'game_config',
    'GAME_CONF'           => 'game_config',
    'TIME_CONF'           => 'game_config',
    'ICON_CONF'           => 'game_config',
    'ROOM_IMG'            => 'game_config',
    'ROLE_IMG'            => 'game_config',
    'SOUND'               => 'game_config',
    'CAST_CONF'           => 'cast_config',
    'MESSAGE'             => 'message',
    'GAME_OPT_MESS'       => 'message',
    'VICT_MESS'           => 'message',
    'VOTE_MESS'           => 'message',
    'SCRIPT_INFO'         => 'version',
    'RQ_ARGS'             => 'request_class',
    'ROLES'               => 'role_class',
    'TIME_CALC'           => 'info_functions',
    'SHARED_CONF'         => 'info_functions',
    'COPYRIGHT'           => 'info_functions',
    'MENU_LINK'           => 'index_functions',
    'TWITTER'             => 'twitter',
    'PAPARAZZI'           => 'paparazzi_class',
    'talk_class'          => 'user_class',
    'game_play_functions' => 'user_class',
    'game_vote_functions' => 'game_functions',
    'user_class'          => 'game_functions',
    'server_config'       => 'system_class', //常時ロードされる
    'system_class'        => array('functions', 'room_class'), //常時ロードされる
    'room_class'          => 'option_class',
    'role_class'          => 'game_format',
    'paparazzi_class'     => 'paparazzi'
  );

  //依存クラス情報 (読み込むデータ => 依存するクラス)
  public $depend_class = array(
    'GAME_OPT_CAPT'       => 'GAME_OPT_MESS',
    'TIME_CALC'           => array('ROOM_CONF', 'GAME_CONF', 'TIME_CONF', 'ROOM_IMG',
				   'CAST_CONF', 'ROLE_DATA'),
    'COPYRIGHT'           => 'SCRIPT_INFO',
    'game_play_functions' => 'ROLE_IMG',
    'user_class'          => array('GAME_CONF', 'ROLE_DATA', 'MESSAGE'),
    'icon_functions'      => array('ICON_CONF', 'USER_ICON'),
    'index_functions'     => array('SCRIPT_INFO', 'BBS_CONF'),
    'oldlog_functions'    => array('CAST_CONF', 'ROOM_IMG', 'GAME_OPT_MESS')
  );

  //クラス名情報 (グローバル変数名 => 読み込むクラス)
  public $class_list = array(
    'DB_CONF'       => 'DatabaseConfig',
    'SERVER_CONF'   => 'ServerConfig',
    'SHARED_CONF'   => 'SharedServerConfig',
    'USER_ICON'     => 'UserIcon',
    'MENU_LINK'     => 'MenuLinkBuilder',
    'BBS_CONF'      => 'BBSConfig',
    'COPYRIGHT'     => 'CopyrightConfig',
    'SCRIPT_INFO'   => 'ScriptInfo',
    'SESSION'       => 'Session',
    'ROOM_CONF'     => 'RoomConfig',
    'GAME_CONF'     => 'GameConfig',
    'ROLE_DATA'     => 'RoleData',
    'CAST_CONF'     => 'CastConfig',
    'TIME_CONF'     => 'TimeConfig',
    'ICON_CONF'     => 'IconConfig',
    'ROOM_IMG'      => 'RoomImage',
    'ROLE_IMG'      => 'RoleImage',
    'SOUND'         => 'Sound',
    'COOKIE'        => 'CookieDataSet',
    'MESSAGE'       => 'Message',
    'GAME_OPT_MESS' => 'GameOptionMessage',
    'GAME_OPT_CAPT' => 'GameOptionCaptionMessage',
    'VICT_MESS'     => 'VictoryMessage',
    'VOTE_MESS'     => 'VoteMessage',
    'RQ_ARGS'       => 'RequestBase',
    'ROLES'         => 'RoleManager',
    'TIME_CALC'     => 'TimeCalculation',
    'TWITTER'       => 'TwitterConfig',
    'PAPARAZZI'     => 'Paparazzi'
  );

  function __construct(){
    $this->path = new StdClass();
    $this->path->root    = JINRO_ROOT;
    $this->path->config  = JINRO_CONF;
    $this->path->include = JINRO_INC;
    $this->path->module  = JINRO_MOD;
    $this->loaded = new StdClass();
    $this->loaded->file  = array();
    $this->loaded->class = array();
  }

  //依存情報設定
  protected function SetDepend($type, $name, $depend){
    if(is_null($this->$type)) return false;
    $this->{$type}[$name] = $depend;
    return true;
  }

  //依存クラス情報設定 ＆ ロード
  protected function SetClass($name, $class){
    if(! $this->SetDepend('class_list', $name, $class)) return false;
    $this->LoadClass($name);
    return true;
  }

  //依存解決処理
  protected function LoadDependence($name){
    if(array_key_exists($name, $this->depend_file)) $this->LoadFile($this->depend_file[$name]);
    if(array_key_exists($name, $this->depend_class)) $this->LoadClass($this->depend_class[$name]);
  }

  //ファイルロード
  function LoadFile($name){
    $name_list = func_get_args();
    if(is_array($name_list[0])) $name_list = $name_list[0];
    if(count($name_list) > 1){
      foreach($name_list as $name) $this->LoadFile($name);
      return;
    }

    if(is_null($name) || in_array($name, $this->loaded->file)) return false;
    $this->LoadDependence($name);

    switch($name){
    case 'server_config':
    case 'game_config':
    case 'cast_config':
    case 'message':
    case 'version':
      $path = $this->path->config;
      break;

    case 'mb-emulator':
    case 'twitter':
      $path = $this->path->module . '/' . $name;
      break;

    case 'option_class':
    case 'role_class':
    case 'chatengine':
    case 'feedengine':
    case 'paparazzi':
    case 'paparazzi_class':
      $path = $this->path->include . '/' . @array_shift(explode('_', $name));
      break;

    default:
      $path = $this->path->include;
      break;
    }

    #echo $path . '/' . $name . '.php';
    require_once($path . '/' . $name . '.php');
    $this->loaded->file[] = $name;
    return true;
  }

  function LoadClass($name){
    $name_list = func_get_args();
    if(is_array($name_list[0])) $name_list = $name_list[0];
    if(count($name_list) > 1){
      foreach($name_list as $name) $this->LoadClass($name);
      return;
    }

    if(is_null($name) || in_array($name, $this->loaded->class)) return false;
    $this->LoadDependence($name);

    if(is_null($class_name = $this->class_list[$name])) return false;
    $GLOBALS[$name] = new $class_name();
    $this->loaded->class[] = $name;
    return true;
  }

  function LoadRequest($class = NULL){ return $this->SetClass('RQ_ARGS', $class); }
}

//-- 初期化処理 --//
$INIT_CONF = new InitializeConfig();

//mbstring 非対応の場合、エミュレータを使用する
if(! extension_loaded('mbstring')) $INIT_CONF->LoadFile('mb-emulator');

$INIT_CONF->LoadClass('DB_CONF', 'SERVER_CONF');
if(FindDangerValue($_REQUEST) || FindDangerValue($_SERVER)) die;

//デバッグ用ツールをロード
$SERVER_CONF->debug_mode ? $INIT_CONF->LoadClass('PAPARAZZI') : $INIT_CONF->LoadFile('paparazzi');

//PrintData($INIT_CONF); //テスト用

//-- スクリプト群の文字コード --//
/*
  変更する場合は全てのファイル自体の文字コードを自前で変更してください
  declare encoding は --enable-zend-multibyte が有効な PHP でのみ機能します
*/
//declare(encoding='UTF-8');

//-- マルチバイト入出力指定 --//
if(extension_loaded('mbstring')){
  mb_language('ja');
  mb_internal_encoding($SERVER_CONF->encode);
  mb_http_input('auto');
  mb_http_output($SERVER_CONF->encode);
}

//-- ヘッダ強制指定 --//
if($SERVER_CONF->set_header_encode && ! headers_sent()){ //ヘッダ未送信時にセットする
  header("Content-type: text/html; charset={$SERVER_CONF->encode}");
  header('Content-Language: ja');
}
