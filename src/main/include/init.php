<?php
//error_reporting(E_ALL);
//-- 定数を定義 --//
/*
  ServerConfig::SITE_ROOT を使って CSS や画像等をロードする仕様にすると
  ローカルに保存する場合や、ログを別のサーバに移す場合に手間がかかるので
  JINRO_ROOT で相対パスを定義して共通で使用する仕様に変更しました。
  絶対パスが返る dirname() を使ったパスの定義を行わないで下さい。
*/
if (! defined('JINRO_ROOT')) define('JINRO_ROOT', '.');
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
class Loader {
  const PATH = '%s/%s.php';
  static $file  = array(); //ロード済みファイル
  static $class = array(); //ロード済みクラス

  //依存ファイル情報 (読み込むデータ => 依存するファイル)
  static $depend_file = array(
    'PAPARAZZI'            => 'paparazzi_class',
    'InfoTime'             => array('room_config', 'time_config', 'game_config', 'cast_config',
				    'role_data_class', 'image_class', 'info_functions'),
    'server_config'        => array('system_class', 'functions'), //常時ロードされる
    'chaos_config'         => 'cast_config',
    'shared_server_config' => 'info_functions',
    'copyright_config'     => array('version', 'info_functions'),
    'database_class'       => 'database_config',
    'system_class'         => 'room_class', //常時ロードされる
    'room_class'           => 'option_class',
    'room_option_class'    => array('room_config', 'time_config', 'game_config',
				    'game_option_config', 'option_class', 'room_option_item_class'),
    'option_form_class'    => 'room_option_class',
    'user_class'           => array('role_data_class', 'game_functions'),
    'talk_class'           => array('game_config', 'message', 'user_class', 'role_class'),
    'icon_class'           => 'icon_config',
    'user_icon_class'      => 'user_icon_config',
    'sound_class'          => 'sound_config',
    'cookie_class'         => 'sound_class',
    'twitter_class'        => array('twitter_config', 'twitter'),
    'rss_class'            => array('image_class', 'room_option_class', 'feedengine',
				    'site_summary'),
    'test_class'           => array('room_config', 'user_class', 'icon_class', 'role_class',
				    'game_vote_functions', 'test_functions'),
    'paparazzi_class'      => 'paparazzi',
    'room_manager_class'   => array('image_class', 'room_option_class'),
    'login_class'          => 'session_class',
    'game_view_class'      => array('talk_class', 'icon_class'),
    'game_play_class'      => array('time_config', 'session_class', 'image_class', 'talk_class',
				    'icon_class'),
    'game_vote_class'      => array('session_class', 'room_option_class', 'user_class',
				    'icon_class', 'role_class', 'game_vote_functions'),
    'game_log_class'       => array('session_class', 'talk_class'),
    'old_log_class'        => 'old_log_functions',
    'user_manager_class'   => array('room_config', 'game_config', 'message', 'session_class',
				    'room_class', 'user_class', 'icon_functions'),
    'icon_view_class'      => array('session_class', 'icon_functions'),
    'icon_edit_class'      => 'icon_functions',
    'icon_upload_class'    => array('session_class', 'icon_functions'),
    'setup_class'          => array('setup_config', 'version', 'database_class'),
    'index_functions'      => array('menu_config', 'bbs_config', 'version', 'option_form_class'),
    'game_vote_functions'  => array('game_config', 'message', 'game_functions'),
    'icon_functions'       => array('icon_class', 'user_icon_class'),
    'old_log_functions'    => array('old_log_config', 'cast_config', 'image_class',
				    'room_option_class'),
  );

  //依存クラス情報 (読み込むデータ => 依存するクラス)
  static $depend_class = array(
    //'talk_class' => 'ROLES',
  );

  //クラス名情報 (グローバル変数名 => 読み込むクラス)
  static $class_list = array(
    'PAPARAZZI' => 'Paparazzi'
  );

  //ファイルロード
  static function LoadFile($name) {
    $name_list = func_get_args();
    if (is_array($name_list[0])) $name_list = $name_list[0];
    if (count($name_list) > 1) {
      foreach ($name_list as $name) self::LoadFile($name);
      return true;
    }

    if (is_null($name) || in_array($name, self::$file)) return false;
    self::LoadDependence($name);

    require_once(self::GetPath($name));
    self::$file[] = $name;
    return true;
  }

  //クラスロード
  static function LoadClass($name) {
    $name_list = func_get_args();
    if (is_array($name_list[0])) $name_list = $name_list[0];
    if (count($name_list) > 1) {
      foreach ($name_list as $name) self::LoadClass($name);
      return true;
    }

    if (is_null($name) || in_array($name, self::$class)) return false;
    self::LoadDependence($name);

    if (is_null($class_name = self::$class_list[$name])) {
      $class_name = $name;
      new $class_name();
    }
    else {
      $GLOBALS[$name] = new $class_name();
    }
    self::$class[] = $class_name;
    return true;
  }

  //リクエストクラスロード
  static function LoadRequest($class = null, $load = false) {
    if ($load) self::LoadFile('game_config');
    self::LoadFile('request_class');
    return RQ::Load($class);
  }

  //ファイルロード済み判定
  static function IsLoaded($file) { return in_array($file, self::$file); }

  //ファイルパス取得
  private function GetPath($name) {
    switch ($name) {
    case 'copyright_config':
    case 'version':
      $path = JINRO_CONF . '/system';
      break;

    case 'game_config':
    case 'cast_config':
    case 'chaos_config':
    case 'message':
    case 'vote_message':
    case 'winner_message':
    case 'time_config':
    case 'icon_config':
    case 'sound_config':
      $path = JINRO_CONF . '/game';
      break;

    case 'database_config':
    case 'server_config':
    case 'room_config':
    case 'game_option_config':
    case 'user_icon_config':
    case 'menu_config':
    case 'bbs_config':
    case 'old_log_config':
    case 'shared_server_config':
    case 'src_upload_config':
    case 'twitter_config':
    case 'setup_config':
      $path = JINRO_CONF . '/server';
      break;

    case 'mb-emulator':
    case 'twitter':
      $path = JINRO_MOD . '/' . $name;
      break;

    case 'role_class':
    case 'role_data_class':
    case 'chatengine':
    case 'paparazzi':
    case 'paparazzi_class':
    case 'test_class':
    case 'test_functions':
      $path = JINRO_INC . '/' . @array_shift(explode('_', $name));
      break;

    case 'option_class':
    case 'option_form_class':
    case 'room_option_class':
    case 'room_option_item_class':
      $path = JINRO_INC . '/option';
      break;

    case 'rss_class':
    case 'feedengine':
    case 'site_summary':
      $path = JINRO_INC . '/feedengine';
      break;

    default:
      $path = JINRO_INC;
      break;
    }

    return sprintf(self::PATH, $path, $name);
  }

  //依存解決処理
  private function LoadDependence($name) {
    if (array_key_exists($name, self::$depend_file))  self::LoadFile(self::$depend_file[$name]);
    if (array_key_exists($name, self::$depend_class)) self::LoadClass(self::$depend_class[$name]);
  }
}

//-- 初期化処理 --//
Loader::LoadFile('database_class', 'server_config');

//mbstring 非対応の場合、エミュレータを使用する
if (! extension_loaded('mbstring')) Loader::LoadFile('mb-emulator');

if (Security::CheckValue($_REQUEST) || Security::CheckValue($_SERVER)) die;

//デバッグ用ツールをロード
ServerConfig::DEBUG_MODE ? Loader::LoadClass('PAPARAZZI') : Loader::LoadFile('paparazzi');

//-- スクリプト群の文字コード --//
/*
  変更する場合は全てのファイル自体の文字コードを自前で変更してください
  declare encoding は --enable-zend-multibyte が有効な PHP でのみ機能します
*/
//declare(encoding='UTF-8');

//-- マルチバイト入出力指定 --//
if (extension_loaded('mbstring')) {
  mb_language('ja');
  mb_internal_encoding(ServerConfig::ENCODE);
  mb_http_input('auto');
  mb_http_output(ServerConfig::ENCODE);
}

//-- ヘッダ強制指定 --//
if (ServerConfig::SET_HEADER_ENCODE && ! headers_sent()) { //ヘッダ未送信時にセットする
  header(sprintf('Content-type: text/html; charset=%s', ServerConfig::ENCODE));
  header('Content-Language: ja');
}
