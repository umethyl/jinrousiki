<?php
//-- データベース初期セットアップコントローラー --//
final class JinrouAdminSetupController extends JinrouAdminController {
  private static $table_list = []; //テーブルリスト

  protected static function GetAdminType() {
    return 'setup';
  }

  protected static function Output() {
    HTML::OutputHeader(SetupMessage::TITLE, null, true);
    DB::Enable(true, true);

    $name = DatabaseConfig::NAME;
    Text::p($name, SetupMessage::TARGET_DB);
    if (false === DB::ConnectInHeader()) {
      self::CreateDatabase($name);
    }
    self::SetupTable($name);

    foreach (DatabaseConfig::$name_list as $id => $name) {
      DB::Disconnect();
      Text::p($name, SetupMessage::TARGET_DB);
      if (false === DB::ConnectInHeader($id + 1)) {
	self::CreateDatabase($name);
      }
      self::SetupTable($name);
    }

    Text::p(SetupMessage::COMPLETE);
    HTML::OutputFooter();
  }

  //テーブル存在確認
  private static function Exists($table) {
    return in_array($table, self::$table_list);
  }

  //対象バージョン確認
  private static function IsRevision($revision) {
    return Number::Within(ServerConfig::REVISION, 0, $revision);
  }

  //データベース作成
  private static function CreateDatabase($name) {
    SetupDB::Connect();
    $result = SetupDB::CreateDatabase($name);
    if (true === $result) {
      SetupDB::Grant($name);
    }
    self::OutputResult(SetupMessage::CREATE_DB, $name, $result);
    DB::Reconnect($name);
  }

  //テーブル作成
  private static function CreateTable($table) {
    $result = SetupDB::CreateTable($table);
    self::OutputResult(SetupMessage::CREATE_TABLE, $table, $result);
  }

  //インデックス作成
  private static function CreateIndex($table, $index, $value) {
    $result = SetupDB::CreateIndex($table, $index, $value);
    self::OutputResult(SetupMessage::CREATE_INDEX, $table, $result);
  }

  //インデックス再生成
  private static function RegenerateIndex($table, $index, $value) {
    $title  = SetupMessage::REGENERATE_INDEX;
    $result = SetupDB::RegenerateIndex($table, $index, $value);
    self::OutputResult($title, $table, $result);
  }

  //型変更
  private static function ChangeColumn($table, array $column_list) {
    $title = SetupMessage::CHANGE_COLUMN . $table;
    foreach ($column_list as $column) {
      $result = SetupDB::ChangeColumn($table, $column);
      self::OutputResult($title, $column, $result);
    }
  }

  //テーブル削除
  private static function DropTable($table) {
    $result = SetupDB::DropTable($table);
    if (true === $result) {
      unset(self::$table_list[array_search($table, self::$table_list)]);
    }
    self::OutputResult(SetupMessage::DROP_TABLE, $table, $result);
  }

  //カラム削除
  private static function DropColumn($table, $column) {
    $stack = SetupDB::ShowColumn($table);
    if (false === in_array($column, $stack)) {
      return true;
    }
    $title  = SetupMessage::DROP_COLUMN . $table;
    $result = SetupDB::DropColumn($table, $column);
    self::OutputResult($title, $column, $result);
  }

  //初期データ登録
  private static function Insert($table) {
    switch ($table) {
    case 'user_entry':
      //管理者登録
      $list = [
	'room_no'     => 0,
	'user_no'     => 0,
	'uname'       => GM::SYSTEM,
	'handle_name' => Message::SYSTEM,
	'icon_no'     => 1,
	'profile'     => GM::PROFILE,
	'password'    => ServerConfig::PASSWORD,
	'role'        => GM::ROLE,
	'live'        => UserLive::LIVE
      ];
      DB::Insert($table, $list);
      break;

    case 'user_icon':
      //アイコン登録
      $items = 'icon_no, icon_name, icon_filename, icon_width, icon_height, color';

      //身代わり君 (No. 0)
      $list = [
	'icon_no'       => 0,
	'icon_name'     => SetupConfig::$dummy_boy_icon['name'],
	'icon_filename' => SetupConfig::$dummy_boy_icon['file'],
	'icon_width'    => SetupConfig::$dummy_boy_icon['width'],
	'icon_height'   => SetupConfig::$dummy_boy_icon['height'],
	'color'         => SetupConfig::$dummy_boy_icon['color']
      ];
      DB::Insert($table, $list);

      //初期アイコン
      foreach (SetupConfig::$default_icon as $id => $stack) {
	$list = [
	  'icon_no'       => $id,
	  'icon_name'     => $stack['name'],
	  'icon_filename' => $stack['file'],
	  'icon_width'    => $stack['width'],
	  'icon_height'   => $stack['height'],
	  'color'         => $stack['color']
	];
	$result = DB::Insert($table, $list);
	self::OutputResult(SetupMessage::ICON, print_r($list, true), $result);
      }
      break;

    case 'count_limit':
      //ロックキー
      foreach (['room', 'icon'] as $value) {
	DB::Insert($table, ['type' => $value]);
      }
      break;
    }
  }

  //更新処理
  private static function Update($table) {
    switch ($table) {
    case 'room':
      if (self::IsRevision(863)) {
	$stack = [
	  'room_no', 'name', 'comment', 'max_user', 'game_option', 'option_role', 'date',
	  'vote_count', 'revote_count', 'winner', 'establisher_ip'
	];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'user_entry':
      if (self::IsRevision(863)) {
	$stack = [
	  'room_no', 'user_no', 'uname', 'handle_name', 'icon_no', 'sex', 'password', 'role',
	  'role_id', 'objection', 'live', 'ip_address'
	];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'player':
      if (self::IsRevision(863)) {
	$stack = ['id', 'room_no', 'date', 'user_no', 'role'];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'talk':
      if (self::IsRevision(494)) {
	self::RegenerateIndex($table, $table . '_index', 'room_no, date, scene');
      }

      if (self::IsRevision(863)) {
	self::DropColumn($table, 'objection');

	$stack = [
	  'id', 'room_no', 'date', 'location', 'uname', 'role_id', 'action', 'font_type',
	  'spend_time'
	];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'talk_beforegame':
      if (self::IsRevision(494)) {
	self::RegenerateIndex($table, $table . '_index', 'room_no');
      }

      if (self::IsRevision(863)) {
	$stack = [
	  'id', 'room_no', 'date', 'location', 'uname', 'handle_name', 'action', 'font_type',
	  'spend_time'
	];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'talk_aftergame':
      if (self::IsRevision(494)) {
	self::RegenerateIndex($table, $table . '_index', 'room_no');
      }

      if (self::IsRevision(863)) {
	$stack = [
	  'id', 'room_no', 'date', 'location', 'uname', 'action', 'font_type', 'spend_time'
	];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'vote':
      if (self::IsRevision(863)) {
	$stack = [
	  'room_no', 'date', 'type', 'uname', 'user_no', 'target_no', 'vote_number',
	  'vote_count', 'revote_count'
	];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'system_message':
      if (self::IsRevision(863)) {
	$stack = ['room_no', 'date', 'type', 'message'];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'result_ability':
      if (self::IsRevision(863)) {
	$stack = ['room_no', 'date', 'type', 'user_no', 'target', 'result'];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'result_dead':
      if (self::IsRevision(863)) {
	$stack = ['room_no', 'date', 'type', 'handle_name', 'result'];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'result_lastwords':
      if (self::IsRevision(863)) {
	$stack = ['room_no', 'date', 'handle_name'];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'result_vote_kill':
      if (self::IsRevision(863)) {
	$stack = [
	  'id', 'room_no', 'date', 'count', 'handle_name', 'target_name', 'vote', 'poll'
	];
	self::ChangeColumn($table, $stack);
      }
      break;

    case 'user_icon':
      if (self::IsRevision(863)) {
	$stack = [
	  'icon_no', 'icon_name', 'icon_filename', 'icon_width', 'icon_height', 'color',
	  'session_id', 'category', 'appearance', 'author'
	];
	self::ChangeColumn($table, $stack);
      }
      break;
    }
  }

  //再構成処理
  private static function Reset($table) {
    switch ($table) {
    case 'count_limit':
      if (self::IsRevision(863)) {
	self::DropTable($table);
      }
      break;

    case 'document_cache':
      if (self::IsRevision(792) || self::IsRevision(863)) {
	self::DropTable($table);
      }
      break;
    }
  }

  //必要なテーブルがあるか確認する
  private static function SetupTable($name) {
    if (ServerConfig::REVISION >= ScriptInfo::REVISION) {
      Text::p($name, SetupMessage::ALREADY);
      return;
    }
    self::$table_list = SetupDB::ShowTable(); //テーブルのリストをセット

    $stack = [
      'room', 'user_entry', 'player',
      'talk', 'talk_beforegame', 'talk_aftergame', 'user_talk_count',
      'vote', 'system_message',
      'result_ability', 'result_dead', 'result_lastwords', 'result_vote_kill',
      'user_icon',
      'count_limit', 'document_cache'
    ];
    foreach ($stack as $table) {
      if (true === self::Exists($table)) {
	self::Reset($table);
      }
      if (false === self::Exists($table)) {
	self::CreateTable($table);
	self::Insert($table);
      }
      self::Update($table);
    }
    Text::p($name, SetupMessage::FINISHED);
  }

  //結果出力
  private static function OutputResult($title, $name, $result) {
    $str = $result ? SetupMessage::SUCCESS : SetupMessage::FAILED;
    Text::p(sprintf('%s: %s', $name, $str), $title);
  }
}
