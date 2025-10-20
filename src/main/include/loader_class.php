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
    if (true === self::LoadFile($name) && true === self::LoadClass($name)) {
      return static::$class[$name];
    } else {
      return null;
    }
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
      if (null === $name) {
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
      //printf('◆Register: %s: %s<br>', $name, $file);
      static::$file[] = $name;
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
      if (null === $name) {
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
    //printf('◆Class: %s<br>', $class_name);
    static::$class[$name] = new $class_name();
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
    if (ServerConfig::DISPLAY_ERROR) { //エラー表示設定
      ini_set('display_errors', 'On');
      error_reporting(E_ALL);
    }

    //mbstring 非対応の場合、エミュレータを使用する
    if (false === extension_loaded('mbstring')) {
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
    if (true === extension_loaded('mbstring')) {
      mb_language('ja');
      mb_internal_encoding(ServerConfig::ENCODE);
      mb_http_input();
      //mb_http_input('auto');
      mb_http_output(ServerConfig::ENCODE);
    }

    //-- ヘッダ強制指定 --//
    //ヘッダ未送信時にセットする
    if (ServerConfig::SET_HEADER_ENCODE && false === headers_sent()) {
      header(sprintf('Content-type: text/html; charset=%s', ServerConfig::ENCODE));
      header('Content-Language: ja');
    }
  }

  //オートロード
  public static function AutoLoad($name) {
    $file = self::GetFile($name);
    if (null === $file) {
      throw new RuntimeException("AutoLoad failed: {$name}");
    } else {
      self::LoadFile($file);
    }
  }

  //依存解決処理
  protected static function LoadDependence($name) {
    if (true === isset(LoaderData::$depend[$name])) {
      self::LoadFile(LoaderData::$depend[$name]);
    }
  }

  //ファイル取得
  protected static function GetFile($name) {
    return isset(LoaderData::$file[$name]) ? LoaderData::$file[$name] : null;
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
    case 'controller/admin':
    case 'controller/info':
    case 'controller/info/new_role':
    case 'controller/test':
    case 'data':
    case 'data/option':
    case 'data/event':
    case 'data/role':
    case 'data/vote':
    case 'database':
    case 'request':
    case 'time':
    case 'option':
    case 'event':
    case 'role':
    case 'talk':
    case 'media':
    case 'html':
    case 'html/media':
    case 'html/option':
    case 'html/role':
    case 'html/controller':
    case 'html/controller/info':
    case 'html/controller/test':
    case 'test':
    case 'paparazzi':
      $path = JINROU_INC . '/' . $type;
      break;

    case 'admin':
    case 'server':
    case 'game':
    case 'system':
    case 'message':
    case 'message/controller':
    case 'message/controller/admin':
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
    //config
    'server_config'	=> ['system_class', 'functions', 'request_data_class'], //常時ロード
    //library
    'twitter_class'	=> 'twitter',
    'paparazzi_class'	=> 'paparazzi',
    //controller/test
    'user_entry_test_class'	=> 'user_manager_class',
  ];

  //クラス => ファイル対応表
  public static $file = [
    //config/admin
    'AdminConfig'		=> 'admin_config',
    'SetupConfig'		=> 'setup_config',
    'GenerateHTMLLogConfig'	=> 'generate_html_log_config',
    //config/server
    'ServerConfig'		=> 'server_config',
    'DatabaseConfig'		=> 'database_config',
    'RoomConfig'		=> 'room_config',
    'GameOptionConfig'		=> 'game_option_config',
    'CacheConfig'		=> 'cache_config',
    'UserIconConfig'		=> 'user_icon_config',
    'OldLogConfig'		=> 'old_log_config',
    'TopPageConfig'		=> 'top_page_config',
    'MenuConfig'		=> 'menu_config',
    'BBSConfig'			=> 'bbs_config',
    'SharedServerConfig'	=> 'shared_server_config',
    'TwitterConfig'		=> 'twitter_config',
    //config/game
    'GameConfig'	=> 'game_config',
    'CastConfig'	=> 'cast_config',
    'ChaosConfig'	=> 'chaos_config',
    'DuelConfig'	=> 'duel_config',
    'TimeConfig'	=> 'time_config',
    'IconConfig'	=> 'icon_config',
    'SoundConfig'	=> 'sound_config',
    //config/system
    'CopyrightConfig'	=> 'copyright_config',
    'ScriptInfo'	=> 'version',
    //config/message
    'Message'			=> 'message',
    'GameMessage'		=> 'game_message',
    'DeadMessage'		=> 'dead_message',
    'TalkMessage'		=> 'talk_message',
    'VoteMessage'		=> 'vote_message',
    'RoleTalkMessage'		=> 'role_talk_message',
    'VoteTalkMessage'		=> 'vote_talk_message',
    'VoteRoleMessage'		=> 'vote_role_message',
    'WinnerMessage'		=> 'winner_message',
    'RoleAbilityMessage'	=> 'role_ability_message',
    'OptionMessage'		=> 'option_message',
    'IconMessage'		=> 'icon_message',
    'InfoMessage'		=> 'info_message',
    'CacheMessage'		=> 'cache_message',
    'TwitterMessage'		=> 'twitter_message',
    'TestMessage'		=> 'test_message',
    //data
    'DeadReason'	=> 'dead_reason_data_class',
    //data/room
    'RoomScene'		=> 'room_data_class',
    'RoomStatus'	=> 'room_data_class',
    'RoomMode'		=> 'room_data_class',
    //data/user
    'UserLive'	=> 'user_data_class',
    'UserMode'	=> 'user_data_class',
    //data/talk
    'TalkLocation'	=> 'talk_data_class',
    'TalkAction'	=> 'talk_data_class',
    'TalkVoice'		=> 'talk_data_class',
    'TalkElement'	=> 'talk_data_class',
    'TalkCSS'		=> 'talk_data_class',
    //data/option
    'OptionGroup'		=> 'option_data_class',
    'OptionFormType'		=> 'option_data_class',
    'OptionFormData'		=> 'option_form_data_class',
    'OptionFilterData'		=> 'option_filter_data_class',
    'RoomOptionOrderData'	=> 'room_option_order_data_class',
    'RoomOptionFilterData'	=> 'room_option_filter_data_class',
    //data/event
    'EventType'		=> 'event_data_class',
    'EventFilterData'	=> 'event_filter_data_class',
    'WeatherData'	=> 'weather_data_class',
    //data/role
    'RoleData'		=> 'role_data_class',
    'RoleSubData'	=> 'role_sub_data_class',
    'RoleShortData'	=> 'role_short_data_class',
    'RoleGroupData'	=> 'role_group_data_class',
    'RoleGroupSubData'	=> 'role_group_sub_data_class',
    'RoleFilterData'	=> 'role_filter_data_class',
    //data/camp
    'BaseCamp'	=> 'camp_data_class',
    'Camp'	=> 'camp_data_class',
    'CampGroup'	=> 'camp_data_class',
    'WinCamp'	=> 'camp_data_class',
    //date/vote
    'VoteAction'			=> 'vote_data_class',
    'VoteKickElement'			=> 'vote_data_class',
    'VoteDayElement'			=> 'vote_data_class',
    'VoteForceSuddenDeathElement'	=> 'vote_data_class',
    'VoteCSS'				=> 'vote_data_class',
    'VoteActionGroup'			=> 'vote_group_data_class',
    'RoleActionDate'			=> 'role_vote_data_class',
    'RoleAbility'			=> 'role_vote_data_class',
    'RoleStackVoteKill'			=> 'role_vote_data_class',
    //system
    'JinrouAdmin'		=> 'admin_class',
    'JinrouAdminController'	=> 'admin_class',
    'JinrouTestController'	=> 'admin_class',
    'Cast'			=> 'cast_class',
    'PageLinkBuilder'		=> 'old_log_functions',
    'JinrouCacheManager'	=> 'cache_class',
    //system/game
    'GameAction'	=> 'game_functions',
    'Position'		=> 'game_functions',
    'Objection'		=> 'game_functions',
    'Winner'		=> 'game_functions',
    //system/room
    'Room'		=> 'room_class',
    //system/user
    'User'		=> 'user_class',
    'UserLoader'	=> 'user_class',
    //database
    'DB'		=> 'db_class',
    'Query'		=> 'db_class',
    'RoomDB'		=> 'room_db_class',
    'SystemMessageDB'	=> 'room_db_class',
    'RoomLoaderDB'	=> 'room_db_class',
    'RoomTalkDB'	=> 'room_db_class',
    'UserDB'		=> 'user_db_class',
    'UserLoaderDB'	=> 'user_db_class',
    'TalkDB'		=> 'talk_db_class',
    'IconDB'		=> 'icon_db_class',
    'SessionDB'		=> 'session_db_class',
    'JinrouCacheDB'	=> 'cache_db_class',
    'RoomManagerDB'	=> 'room_manager_db_class',
    'LoginDB'		=> 'login_db_class',
    'SetupDB'		=> 'setup_db_class',
    //request
    'RQ'		=> 'request_class',
    'Session'		=> 'session_class',
    'JinrouCookie'	=> 'cookie_class',
    //option
    'OptionManager'	=> 'option_class',
    'OptionLoader'	=> 'option_class',
    'OptionParser'	=> 'option_class',
    'OptionForm'	=> 'option_form_class',
    'RoomOptionLoader'	=> 'room_option_loader_class',
    'RoomOptionManager'	=> 'room_option_manager_class',
    //time
    'Time'		=> 'time_class',
    'GameTime'		=> 'game_time_class',
    'InfoTime'		=> 'info_time_class',
    //talk
    'Talk'		=> 'talk_class',
    'TalkParser'	=> 'talk_class',
    'TalkBuilder'	=> 'talk_class',
    'GamePlayTalk'	=> 'game_play_talk_class',
    'AutoPlayTalk'	=> 'auto_play_talk_class',
    //media
    'ImageManager'	=> 'image_class',
    'Icon'		=> 'icon_class',
    'UserIcon'		=> 'user_icon_class',
    'Sound'		=> 'sound_class',
    'JinrouTwitter'	=> 'twitter_class',
    //role
    'RoleManager'	=> 'role_class',
    'RoleLoader'	=> 'role_class',
    'RoleTalk'		=> 'role_class',
    'RoleTalkStruct'	=> 'role_class',
    'RoleDataManager'	=> 'role_data_manager_class',
    'RoleUser'		=> 'role_user_class',
    'RoleVote'		=> 'role_vote_class',
    //event
    'EventManager'	=> 'event_class',
    'WeatherManager'	=> 'weather_class',
    //vote
    'VoteKick'			=> 'game_vote_functions',
    'VoteGameStart'		=> 'game_vote_functions',
    'VoteDay'			=> 'game_vote_functions',
    'VoteNight'			=> 'game_vote_functions',
    'VoteHeaven'		=> 'game_vote_functions',
    'VoteForceSuddenDeath'	=> 'game_vote_functions',
    'VoteResetTime'		=> 'game_vote_functions',
    //controller/test
    'VoteTestController' => 'vote_test_class',
    //controller/message
    'AdminMessage'		=> 'admin_message',
    'TopPageMessage'		=> 'top_page_message',
    'RoomManagerMessage'	=> 'room_manager_message',
    'LoginMessage'		=> 'login_message',
    'UserManagerMessage'	=> 'user_manager_message',
    'GameViewMessage'		=> 'game_view_message',
    'GameUpMessage'		=> 'game_up_message',
    'GamePlayMessage'		=> 'game_play_message',
    'GameLogMessage'		=> 'game_log_message',
    'OldLogMessage'		=> 'old_log_message',
    'IconEditMessage'		=> 'icon_edit_message',
    'IconUploadMessage'		=> 'icon_upload_message',
    //controller/info/admin
    'RoomDeleteMessage'		=> 'room_delete_message',
    'IconDeleteMessage'		=> 'icon_delete_message',
    'GenerateHTMLLogMessage'	=> 'generate_html_log_message',
    'SetupMessage'		=> 'setup_message',
    //controller/info/message
    'ScriptInfoMessage'		=> 'script_info_message',
    'RuleInfoMessage'		=> 'rule_info_message',
    'CastInfoMessage'		=> 'cast_info_message',
    'GameOptionInfoMessage'	=> 'game_option_info_message',
    'ChaosInfoMessage'		=> 'chaos_info_message',
    'DuelInfoMessage'		=> 'duel_info_message',
    'WeatherInfoMessage'	=> 'weather_info_message',
    'SpecInfoMessage'		=> 'spec_info_message',
    'SharedRoomInfoMessage'	=> 'shared_room_info_message',
    'CopyrightInfoMessage'	=> 'copyright_info_message',
    'SearchRoleInfoMessage'	=> 'search_role_info_message',
    //config/test/message
    'NameTestMessage'		=> 'name_test_message',
    'RoleTestMessage'		=> 'role_test_message',
    'CastTestMessage'		=> 'cast_test_message',
    'ChaosVersoTestMessage'	=> 'chaos_verso_test_message',
    'VoteTestMessage'		=> 'vote_test_message',
    'ObjectionTestMessage'	=> 'objection_test_message',
    'TripTestMessage'		=> 'trip_test_message',
    //html
    'HTML'		=> 'html_class',
    'TableHTML'		=> 'table_html_class',
    'GameHTML'		=> 'game_html_class',
    'RoomHTML'		=> 'room_html_class',
    'TalkHTML'		=> 'talk_html_class',
    'VoteHTML'		=> 'vote_html_class',
    'OldLogHTML'	=> 'old_log_html_class',
    'InfoHTML'		=> 'info_html_class',
    //html/media
    'ImageHTML'		=> 'image_html_class',
    'IconHTML'		=> 'icon_html_class',
    'SoundHTML'		=> 'sound_html_class',
    //html/option
    'OptionHTML'	=> 'option_html_class',
    'OptionFormHTML'	=> 'option_form_html_class',
    //html/role
    'RoleHTML'		=> 'role_html_class',
    'RoleDataHTML'	=> 'role_data_html_class',
    //html/controller
    'IndexHTML'		=> 'index_html_class',
    'RoomManagerHTML'	=> 'room_manager_html_class',
    'GameViewHTML'	=> 'game_view_html_class',
    'GameFrameHTML'	=> 'game_frame_html_class',
    'GameUpHTML'	=> 'game_up_html_class',
    'GamePlayHTML'	=> 'game_play_html_class',
    'UserManagerHTML'	=> 'user_manager_html_class',
    'IconViewHTML'	=> 'icon_view_html_class',
    'IconUploadHTML'	=> 'icon_upload_html_class',
    //html/controller/info
    'DuelInfoHTML'		=> 'duel_info_html_class',
    'CopyrightInfoHTML'		=> 'copyright_info_html_class',
    'SearchRoleInfoHTML'	=> 'search_role_info_html_class',
    //html/controller/test
    'DevHTML'		=> 'test_html_class',
    'VoteTestHTML'	=> 'vote_test_html_class',
    'ObjectionTestHTML'	=> 'objection_test_html_class',
    'TripTestHTML'	=> 'trip_test_html_class',
    'TwitterTestHTML'	=> 'twitter_test_html_class',
    //info
    'Info'	=> 'info_functions',
    //module
    'OAuthException'	=> 'twitter',
    //test
    'DevRoom' => 'test_functions'
  ];

  //パス情報 (ファイル名 => パス区分)
  public static $path = [
    /* include */
    //config
    'admin_config'		=> 'admin',
    'setup_config'		=> 'admin',
    'generate_html_log_config'	=> 'admin',
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
    'game_config'		=> 'game',
    'cast_config'		=> 'game',
    'chaos_config'		=> 'game',
    'duel_config'		=> 'game',
    'time_config'		=> 'game',
    'icon_config'		=> 'game',
    'sound_config'		=> 'game',
    'copyright_config'		=> 'system',
    'version'			=> 'system',
    'admin_message'		=> 'message',
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
    //config/message/admin
    'room_delete_message'	=> 'message/controller/admin',
    'icon_delete_message'	=> 'message/controller/admin',
    'generate_html_log_message'	=> 'message/controller/admin',
    'setup_message'		=> 'message/controller/admin',
    //config/message/info
    'script_info_message'	=> 'message/controller/info',
    'rule_info_message'		=> 'message/controller/info',
    'cast_info_message'		=> 'message/controller/info',
    'game_option_info_message'	=> 'message/controller/info',
    'chaos_info_message'	=> 'message/controller/info',
    'duel_info_message'		=> 'message/controller/info',
    'weather_info_message'	=> 'message/controller/info',
    'spec_info_message'		=> 'message/controller/info',
    'shared_room_info_message'	=> 'message/controller/info',
    'copyright_info_message'	=> 'message/controller/info',
    'search_role_info_message'	=> 'message/controller/info',
    //config/message/test
    'name_test_message'		=> 'message/controller/test',
    'role_test_message'		=> 'message/controller/test',
    'cast_test_message'		=> 'message/controller/test',
    'chaos_verso_test_message'	=> 'message/controller/test',
    'vote_test_message'		=> 'message/controller/test',
    'objection_test_message'	=> 'message/controller/test',
    'trip_test_message'		=> 'message/controller/test',
    //data
    'room_data_class'		=> 'data',
    'user_data_class'		=> 'data',
    'request_data_class'	=> 'data',
    'talk_data_class'		=> 'data',
    'dead_reason_data_class'	=> 'data',
    //data/option
    'option_data_class'			=> 'data/option',
    'option_form_data_class'		=> 'data/option',
    'option_filter_data_class'		=> 'data/option',
    'room_option_order_data_class'	=> 'data/option',
    'room_option_filter_data_class'	=> 'data/option',
    //data/event
    'event_data_class'		=> 'data/event',
    'event_filter_data_class'	=> 'data/event',
    'weather_data_class'	=> 'data/event',
    //data/role
    'role_data_class'		=> 'data/role',
    'role_sub_data_class'	=> 'data/role',
    'role_short_data_class'	=> 'data/role',
    'camp_data_class'		=> 'data/role',
    'role_group_data_class'	=> 'data/role',
    'role_group_sub_data_class'	=> 'data/role',
    'role_filter_data_class'	=> 'data/role',
    //data/vote
    'vote_data_class'		=> 'data/vote',
    'vote_group_data_class'	=> 'data/vote',
    'role_vote_data_class'	=> 'data/vote',
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
    //time
    'time_class'		=> 'time',
    'game_time_class'		=> 'time',
    'info_time_class'		=> 'time',
    //option
    'option_class'		=> 'option',
    'option_form_class'		=> 'option',
    'room_option_loader_class'	=> 'option',
    'room_option_manager_class'	=> 'option',
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
    //controller/admin
    'setup_class'		=> 'controller/admin',
    'room_delete_class'		=> 'controller/admin',
    'icon_delete_class'		=> 'controller/admin',
    'log_delete_class'		=> 'controller/admin',
    'generate_html_log_class'	=> 'controller/admin',
    //controller/info
    'script_info_class'		=> 'controller/info',
    'rule_info_class'		=> 'controller/info',
    'cast_info_class'		=> 'controller/info',
    'game_option_info_class'	=> 'controller/info',
    'chaos_info_class'		=> 'controller/info',
    'duel_info_class'		=> 'controller/info',
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
    'test_functions'	=> 'test'
  ];
}
