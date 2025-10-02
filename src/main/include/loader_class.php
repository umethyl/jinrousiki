<?php
//-- ライブラリローダー基底クラス --//
abstract class LoadManager {
  const PATH = '';
  const CLASS_PREFIX = '';
  protected static $file  = [];
  protected static $class = [];

  //-- 共通 --//
  //ライブラリロード
  final public static function Load($name) {
    return (self::LoadFile($name) && self::LoadClass($name)) ? static::$class[$name] : null;
  }

  //依存解決処理
  protected static function LoadDependence($name) {}

  //-- ファイル --//
  //ファイルロード
  final public static function LoadFile(...$name_list) {
    if (true === is_array($name_list[0])) {
      $name_list = $name_list[0];
    }

    foreach ($name_list as $name) {
      //printf('◆LoadFile: %s: %s: <br>', static::CLASS_PREFIX, $name);
      if (true === is_null($name)) {
	throw new InvalidArgumentException('Argument is NULL');
      } elseif (false === self::IsLoadedFile($name)) {
	static::LoadDependence($name);
	self::RegisterFile($name);
      }
    }
    return true;
  }

  //ファイル登録
  final public static function RegisterFile($name) {
    $file = static::GetPath($name);
    if (true == require_once($file)) {
      static::$file[] = $name;
      //printf('◆Register: %s: %s<br>', $name, $file);
      return true;
    } else {
      throw new RuntimeException("Load file failed: {$name}");
    }
  }

  //ファイルロード済み判定
  final public static function IsLoadedFile($name) {
    return in_array($name, static::$file);
  }

  //ファイルパス取得
  protected static function GetPath($name) {
    return sprintf(static::PATH, JINROU_INC, $name);
  }

  //-- クラス --//
  //クラスロード
  final public static function LoadClass(...$name_list) {
    if (true === is_array($name_list[0])) {
      $name_list = $name_list[0];
    }

    foreach ($name_list as $name) {
      //printf('◆LoadClass: %s: %s<br>', static::CLASS_PREFIX, $name);
      if (true === is_null($name)) {
	throw new InvalidArgumentException('Argument is NULL');
      } elseif (false === self::IsLoadedClass($name)) {
	static::LoadDependence($name);
	self::RegisterClass($name);
      }
    }
    return true;
  }

  //クラス登録
  final public static function RegisterClass($name) {
    $class_name  = static::CLASS_PREFIX . $name;
    static::$class[$name] = new $class_name();
    //printf('◆Class: %s<br>', $class_name);
    return true;
  }

  //クラスのロード済み判定
  final public static function IsLoadedClass($name) {
    return isset(static::$class[$name]) && is_object(static::$class[$name]);
  }

  //-- デバッグ用 --//
  final public static function OutputFile() {
    Text::p(static::$file, '◆File: ' . static::CLASS_PREFIX);
  }

  final public static function OutputClass() {
    Text::p(static::$class, '◆Class: ' . static::CLASS_PREFIX);
  }
}

//-- ライブラリローダークラス --//
final class Loader extends LoadManager {
  const PATH = '%s/%s.php';
  protected static $file  = []; //ロード済みファイル
  protected static $class = []; //ロード済みクラス

  //初期化処理
  public static function Initialize() {
    self::LoadFile('db_class', 'server_config');

    if (ServerConfig::DISPLAY_ERROR) { //エラー表示設定
      ini_set('display_errors', 'On');
      error_reporting(E_ALL);
    }

    //mbstring 非対応の場合、エミュレータを使用する
    if (! extension_loaded('mbstring')) {
      self::LoadFile('mb-emulator');
    }

    if (Security::IsInvalidValue($_REQUEST) || Security::IsInvalidValue($_SERVER)) {
      die();
    }

    //デバッグ用ツールをロード
    if (ServerConfig::DEBUG_MODE) {
      self::LoadFile('paparazzi_class');
      JinrouLogger::Load();
    }

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
  }

  //依存解決処理
  protected static function LoadDependence($name) {
    if (true === isset(LoaderData::$depend[$name])) {
      self::LoadFile(LoaderData::$depend[$name]);
    }
  }

  //リクエストクラスロード
  public static function LoadRequest($class = 'Request', $game = false) {
    if (true === $game) {
      self::LoadFile('game_config');
    }
    self::LoadFile('request_class');
    return RQ::LoadRequest($class);
  }

  //ファイルパス取得
  protected static function GetPath($name) {
    if (true === isset(LoaderData::$path[$name])) {
      $type = LoaderData::$path[$name];
    } else {
      $type = $name;
    }

    switch ($type) {
    case 'controller':
    case 'controller/info':
    case 'controller/info/new_role':
    case 'controller/test':
    case 'data':
    case 'database':
    case 'html':
    case 'html/media':
    case 'html/option':
    case 'html/role':
    case 'html/controller':
    case 'html/controller/info':
    case 'html/controller/test':
    case 'media':
    case 'talk':
    case 'request':
    case 'option':
    case 'event':
    case 'role':
    case 'test':
    case 'paparazzi':
      $path = JINROU_INC . '/' . $type;
      break;

    case 'server':
    case 'game':
    case 'system':
    case 'message':
    case 'message/controller':
    case 'message/controller/info':
    case 'message/controller/test':
      $path = JINROU_CONF . '/' . $type;
      break;

    case 'mb-emulator':
    case 'twitter':
      $path = JINROU_MOD . '/' . $name;
      break;

    default:
      $path = JINROU_INC;
      break;
    }

    return sprintf(self::PATH, $path, $name);
  }
}

//-- ライブラリデータクラス --//
/*
  初期化の読み込みを最適化するのが目的なので、依存情報に
  確実に読み込まれているデータを入れる必要はない。
  逆にコード上必須ではないが常にセットで使われるデータを入れると良い。
*/
final class LoaderData {
  //依存ファイル情報 (読み込むデータ => 依存するファイル)
  public static $depend = [
    //class
    'InfoTime'	=> [
	'room_config', 'time_config', 'game_config', 'cast_config', 'role_data_manager_class',
	'image_class'],
    //config
    'server_config'	=> ['message', 'system_class', 'room_class', 'functions'], //常時ロード
    'chaos_config'	=> 'cast_config',
    //database
    'db_class'	=> ['database_config', 'html_class', 'table_html_class'], //常時ロード
    //html
    'icon_html_class'	=> ['icon_class', 'icon_db_class', 'user_icon_class'],
    'info_html_class'	=> 'info_message',
    //function
    'room_class'		=> [ //常時ロード (依存)
	'room_data_class', 'room_db_class', 'request_data_class', 'dead_reason_data_class',
	'vote_data_class', 'event_data_class', 'room_html_class', 'option_class'],
    'user_class'		=> [
	'user_data_class', 'user_db_class', 'role_data_manager_class', 'game_functions'],
    'option_class'		=> [ //常時ロード (依存)
	'option_message', 'option_data_class', 'option_filter_data_class', 'option_html_class',
	'event_class'],
    'room_option_class'		=> [
	'room_config', 'time_config', 'game_config', 'game_option_config'],
    'option_form_class'		=> ['option_form_html_class', 'room_option_class'],
    'event_class'		=> 'event_filter_data_class', //常時ロード (依存)
    'weather_class'		=> 'weather_data_class',
    'cast_class'		=> 'chaos_config',
    'role_class'		=> [
	'role_talk_message', 'role_ability_message', 'role_filter_data_class', 'talk_data_class',
	'role_user_class', 'role_html_class', 'role_data_manager_class'],
    'role_data_manager_class'	=> ['role_data_class', 'role_data_html_class'],
    'role_data_class'		=> [
	'camp_data_class', 'role_sub_data_class', 'role_group_data_class',
	'role_group_sub_data_class', 'role_short_data_class', 'role_vote_data_class',
	'weather_class'],
    'talk_class'		=> [
	'game_config', 'talk_message', 'vote_talk_message', 'role_talk_message', 'talk_data_class',
	'talk_db_class', 'talk_html_class', 'user_class', 'role_class'],
    'session_class'		=> ['session_db_class', 'user_data_class'],
    'image_class'		=> ['camp_data_class', 'image_html_class'],
    'icon_class'		=> ['icon_config', 'image_html_class'],
    'user_icon_class'		=> ['user_icon_config', 'icon_message'],
    'sound_class'		=> ['sound_config', 'sound_html_class'],
    'cookie_class'		=> 'sound_class',
    'cache_class'		=> ['cache_config', 'cache_message', 'cache_db_class'],
    'twitter_class'		=> ['twitter_config', 'twitter_message', 'twitter'],
    'paparazzi_class'		=> 'paparazzi',
    'game_functions'		=> [
	'game_message', 'dead_message', 'camp_data_class', 'game_html_class'],
    'game_vote_functions'	=> [
	'vote_message', 'vote_role_message', 'vote_group_data_class', 'vote_html_class',
	'room_option_class', 'role_vote_class', 'talk_class', 'game_functions'],
    'old_log_functions'		=> [
	'old_log_config', 'cast_config', 'game_message', 'winner_message', 'old_log_message',
	'old_log_html_class', 'image_class', 'room_option_class', 'cache_class'],
    'info_functions'		=> 'info_html_class',
    'test_functions'		=> [
	'room_config', 'test_message', 'test_html_class', 'icon_class', 'game_vote_functions'],
    //controller
    'index_class'		=> [
	'top_page_config', 'menu_config', 'bbs_config', 'version', 'top_page_message',
	'index_html_class', 'info_html_class', 'option_form_class'],
    'room_manager_class'	=> [
	'room_manager_message', 'room_manager_db_class', 'room_manager_html_class', 'image_class',
	'room_option_class'],
    'login_class'		=> [
	'room_config', 'login_message', 'login_db_class', 'session_class'],
    'game_view_class'		=> [
	'game_view_message', 'game_view_html_class', 'talk_class', 'icon_class', 'cache_class'],
    'game_frame_class'		=> ['game_message', 'game_frame_html_class'],
    'game_up_class'		=> [
	'game_up_message', 'talk_data_class', 'game_up_html_class', 'game_html_class'],
    'game_play_class'		=> [
	'time_config', 'game_play_message', 'game_play_html_class', 'game_play_talk_class',
	'session_class', 'image_class', 'talk_class', 'icon_class', 'cache_class'],
    'game_vote_class'		=> [
	'session_class', 'user_class', 'icon_class', 'role_class', 'cache_class',
	'game_vote_functions'],
    'game_log_class'		=> ['game_log_message', 'session_class', 'talk_class'],
    'user_manager_class'	=> [
	'user_manager_message', 'talk_message', 'user_manager_html_class', 'talk_data_class',
	'session_class', 'cookie_class', 'user_class', 'room_option_class', 'cache_class',
	'icon_html_class'],
    'icon_view_class'		=> [
	'icon_view_html_class', 'icon_html_class', 'session_class'],
    'icon_edit_class'		=> ['icon_edit_message', 'icon_html_class'],
    'icon_upload_class'		=> [
	'icon_upload_message', 'icon_upload_html_class', 'icon_html_class', 'session_class'],
    'old_log_class'		=> 'old_log_functions',
    'admin_class'		=> 'admin_message',
    'setup_class'		=> [
	'setup_config', 'version', 'setup_message', 'setup_db_class', 'user_data_class'],
    //controller/info
    'script_info_class'		=> [
	'script_info_message', 'cache_config', 'dead_message', 'talk_message', 'user_icon_class',
	'room_option_class', 'role_data_manager_class'],
    'rule_info_class'		=> 'rule_info_message',
    'cast_info_class'		=> [
	'cast_info_message', 'cast_config', 'role_data_manager_class'],
    'game_option_info_class'	=> [
	'game_option_info_message', 'cast_config', 'room_option_class', 'role_data_manager_class'],
    'chaos_info_class'		=> [
	'chaos_info_message', 'chaos_info_html_class', 'room_option_class'],
    'weather_info_class'	=> [
	'weather_info_message', 'role_data_class', 'room_option_class'],
    'spec_info_class'		=> ['spec_info_message', 'dead_message'],
    'shared_room_info_class'	=> ['shared_room_info_message', 'shared_server_config'],
    'copyright_info_class'	=> [
	'copyright_info_message', 'copyright_config', 'version', 'copyright_info_html_class'],
    'search_role_info_class'	=> ['search_role_info_message', 'search_role_info_html_class'],
    //controller/test
    'name_test_class'		=> 'name_test_message',
    'role_test_class'		=> [
	'role_test_message', 'test_functions', 'cast_class', 'room_option_class'],
    'cast_test_class'		=> ['cast_test_message', 'cast_class', 'room_option_class'],
    'chaos_verso_test_class'	=> ['chaos_verso_test_message', 'cast_class'],
    'user_entry_test_class'	=> ['session_class', 'user_manager_class'],
    'vote_test_class'		=> ['vote_test_message', 'image_class'],
    'step_vote_test_class'	=> ['vote_test_message', 'image_class'],
    'objection_test_class'	=> [
	'objection_test_message', 'objection_test_html_class', 'cast_class'],
    'trip_test_class'		=> ['trip_test_message', 'trip_test_html_class'],
    'twitter_test_class'	=> ['twitter_test_html_class', 'twitter_class']
  ];

  //パス情報 (ファイル名 => パス区分)
  public static $path = [
    /* include */
    //data
    'room_data_class'		=> 'data',
    'user_data_class'		=> 'data',
    'request_data_class'	=> 'data',
    'option_data_class'		=> 'data',
    'option_filter_data_class'	=> 'data',
    'talk_data_class'		=> 'data',
    'camp_data_class'		=> 'data',
    'role_data_class'		=> 'data',
    'role_sub_data_class'	=> 'data',
    'role_group_data_class'	=> 'data',
    'role_group_sub_data_class'	=> 'data',
    'role_short_data_class'	=> 'data',
    'role_vote_data_class'	=> 'data',
    'role_filter_data_class'	=> 'data',
    'event_data_class'		=> 'data',
    'event_filter_data_class'	=> 'data',
    'weather_data_class'	=> 'data',
    'dead_reason_data_class'	=> 'data',
    'vote_data_class'		=> 'data',
    'vote_group_data_class'	=> 'data',
    //database
    'db_class'			=> 'database',
    'room_db_class'		=> 'database',
    'user_db_class'		=> 'database',
    'talk_db_class'		=> 'database',
    'icon_db_class'		=> 'database',
    'session_db_class'		=> 'database',
    'cache_db_class'		=> 'database',
    'room_manager_db_class'	=> 'database',
    'login_db_class'		=> 'database',
    'setup_db_class'		=> 'database',
    //request
    'request_class'	=> 'request',
    'session_class'	=> 'request',
    'cookie_class'	=> 'request',
    //option
    'option_class'	=> 'option',
    'option_form_class'	=> 'option',
    'room_option_class'	=> 'option',
    //talk
    'talk_class'		=> 'talk',
    'game_play_talk_class'	=> 'talk',
    'auto_play_talk_class'	=> 'talk',
    //media
    'image_class'	=> 'media',
    'icon_class'	=> 'media',
    'user_icon_class'	=> 'media',
    'sound_class'	=> 'media',
    //role
    'role_class'		=> 'role',
    'role_user_class'		=> 'role',
    'role_vote_class'		=> 'role',
    'role_data_manager_class'	=> 'role',
    //event
    'event_class'	=> 'event',
    'weather_class'	=> 'event',
    //controller
    'index_class'		=> 'controller',
    'room_manager_class'	=> 'controller',
    'login_class'		=> 'controller',
    'game_view_class'		=> 'controller',
    'game_frame_class'		=> 'controller',
    'game_up_class'		=> 'controller',
    'game_play_class'		=> 'controller',
    'game_vote_class'		=> 'controller',
    'game_log_class'		=> 'controller',
    'user_manager_class'	=> 'controller',
    'icon_view_class'		=> 'controller',
    'icon_edit_class'		=> 'controller',
    'icon_upload_class'		=> 'controller',
    'old_log_class'		=> 'controller',
    'admin_class'		=> 'controller',
    'setup_class'		=> 'controller',
    //controller/info
    'script_info_class'		=> 'controller/info',
    'rule_info_class'		=> 'controller/info',
    'cast_info_class'		=> 'controller/info',
    'game_option_info_class'	=> 'controller/info',
    'chaos_info_class'		=> 'controller/info',
    'weather_info_class'	=> 'controller/info',
    'spec_info_class'		=> 'controller/info',
    'shared_room_info_class'	=> 'controller/info',
    'copyright_info_class'	=> 'controller/info',
    'search_role_info_class'	=> 'controller/info',
    //controller/test
    'name_test_class'		=> 'controller/test',
    'role_test_class'		=> 'controller/test',
    'cast_test_class'		=> 'controller/test',
    'chaos_verso_test_class'	=> 'controller/test',
    'user_entry_test_class'	=> 'controller/test',
    'vote_test_class'		=> 'controller/test',
    'step_vote_test_class'	=> 'controller/test',
    'objection_test_class'	=> 'controller/test',
    'trip_test_class'		=> 'controller/test',
    'twitter_test_class'	=> 'controller/test',
    //html
    'html_class'		=> 'html',
    'table_html_class'		=> 'html',
    'game_html_class'		=> 'html',
    'room_html_class'		=> 'html',
    'talk_html_class'		=> 'html',
    'vote_html_class'		=> 'html',
    'old_log_html_class'	=> 'html',
    'info_html_class'		=> 'html',
    //html/media
    'image_html_class'		=> 'html/media',
    'icon_html_class'		=> 'html/media',
    'sound_html_class'		=> 'html/media',
    //html/option
    'option_html_class'		=> 'html/option',
    'option_form_html_class'	=> 'html/option',
    //html/role
    'role_html_class'		=> 'html/role',
    'role_data_html_class'	=> 'html/role',
    //html/controller
    'index_html_class'		=> 'html/controller',
    'room_manager_html_class'	=> 'html/controller',
    'game_view_html_class'	=> 'html/controller',
    'game_frame_html_class'	=> 'html/controller',
    'game_up_html_class'	=> 'html/controller',
    'game_play_html_class'	=> 'html/controller',
    'user_manager_html_class'	=> 'html/controller',
    'icon_view_html_class'	=> 'html/controller',
    'icon_upload_html_class'	=> 'html/controller',
    //html/controller/info
    'chaos_info_html_class'		=> 'html/controller/info',
    'copyright_info_html_class'		=> 'html/controller/info',
    'search_role_info_html_class'	=> 'html/controller/info',
    //html/controller/test
    'test_html_class'		=> 'html/controller/test',
    'vote_test_html_class'	=> 'html/controller/test',
    'objection_test_html_class'	=> 'html/controller/test',
    'trip_test_html_class'	=> 'html/controller/test',
    'twitter_test_html_class'	=> 'html/controller/test',
    //debug
    'paparazzi'		=> 'paparazzi',
    'paparazzi_class'	=> 'paparazzi',
    //test
    'test_class'	=> 'test',
    'test_functions'	=> 'test',
    //config
    'server_config'		=> 'server',
    'database_config'		=> 'server',
    'room_config'		=> 'server',
    'game_option_config'	=> 'server',
    'cache_config'		=> 'server',
    'user_icon_config'		=> 'server',
    'old_log_config'		=> 'server',
    'top_page_config'		=> 'server',
    'menu_config'		=> 'server',
    'bbs_config'		=> 'server',
    'shared_server_config'	=> 'server',
    'twitter_config'		=> 'server',
    'setup_config'		=> 'server',
    'game_config'		=> 'game',
    'cast_config'		=> 'game',
    'chaos_config'		=> 'game',
    'time_config'		=> 'game',
    'icon_config'		=> 'game',
    'sound_config'		=> 'game',
    'copyright_config'		=> 'system',
    'version'			=> 'system',
    'message'			=> 'message',
    'game_message'		=> 'message',
    'dead_message'		=> 'message',
    'talk_message'		=> 'message',
    'role_talk_message'		=> 'message',
    'vote_talk_message'		=> 'message',
    'vote_message'		=> 'message',
    'vote_role_message'		=> 'message',
    'winner_message'		=> 'message',
    'role_ability_message'	=> 'message',
    'option_message'		=> 'message',
    'icon_message'		=> 'message',
    'info_message'		=> 'message',
    'cache_message'		=> 'message',
    'twitter_message'		=> 'message',
    'test_message'		=> 'message',
    'admin_message'		=> 'message/controller',
    'top_page_message'		=> 'message/controller',
    'room_manager_message'	=> 'message/controller',
    'login_message'		=> 'message/controller',
    'user_manager_message'	=> 'message/controller',
    'game_view_message'		=> 'message/controller',
    'game_up_message'		=> 'message/controller',
    'game_play_message'		=> 'message/controller',
    'game_log_message'		=> 'message/controller',
    'old_log_message'		=> 'message/controller',
    'icon_edit_message'		=> 'message/controller',
    'icon_upload_message'	=> 'message/controller',
    'setup_message'		=> 'message/controller',
    'script_info_message'	=> 'message/controller/info',
    'rule_info_message'		=> 'message/controller/info',
    'cast_info_message'		=> 'message/controller/info',
    'game_option_info_message'	=> 'message/controller/info',
    'chaos_info_message'	=> 'message/controller/info',
    'weather_info_message'	=> 'message/controller/info',
    'spec_info_message'		=> 'message/controller/info',
    'shared_room_info_message'	=> 'message/controller/info',
    'copyright_info_message'	=> 'message/controller/info',
    'search_role_info_message'	=> 'message/controller/info',
    'name_test_message'		=> 'message/controller/test',
    'role_test_message'		=> 'message/controller/test',
    'cast_test_message'		=> 'message/controller/test',
    'chaos_verso_test_message'	=> 'message/controller/test',
    'vote_test_message'		=> 'message/controller/test',
    'objection_test_message'	=> 'message/controller/test',
    'trip_test_message'		=> 'message/controller/test'
  ];
}
