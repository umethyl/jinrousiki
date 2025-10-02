<?php
//-- データベース初期セットアップクラス --//
class JinrouSetup {
  private static $table_list = array(); //テーブルリスト

  //実行処理
  public static function Execute() {
    HTML::OutputHeader(SetupMessage::TITLE, null, true);
    DB::Check(true, true);

    $name = DatabaseConfig::NAME;
    Text::p($name, SetupMessage::TARGET_DB);
    if (! DB::ConnectInHeader()) self::CreateDatabase($name);
    self::CheckTable($name);

    foreach (DatabaseConfig::$name_list as $id => $name) {
      DB::Disconnect();
      Text::p($name, SetupMessage::TARGET_DB);
      if (! DB::ConnectInHeader($id + 1)) self::CreateDatabase($name);
      self::CheckTable($name);
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
    return 0 < ServerConfig::REVISION && ServerConfig::REVISION <= $revision;
  }

  //データベース作成
  private static function CreateDatabase($name) {
    SetupDB::Connect();
    $result = SetupDB::CreateDatabase($name);
    if ($result) SetupDB::Grant($name);
    self::Output(SetupMessage::CREATE_DB, $name, $result);
    DB::Reconnect($name);
  }

  //テーブル作成
  private static function CreateTable($table) {
    self::Output(SetupMessage::CREATE_TABLE, $table, SetupDB::CreateTable($table));
  }

  //インデックス作成
  private static function CreateIndex($table, $index, $value) {
    self::Output(SetupMessage::CREATE_INDEX, $table, SetupDB::CreateIndex($table, $index, $value));
  }

  //インデックス再生成
  private static function RegenerateIndex($table, $index, $value) {
    $title = SetupMessage::REGENERATE_INDEX;
    self::Output($title, $table, SetupDB::RegenerateIndex($table, $index, $value));
  }

  //型変更
  private static function ChangeColumn($table, $column) {
    $title = SetupMessage::CHANGE_COLUMN . $table;
    self::Output($title, $column, SetupDB::ChangeColumn($table, $column));
  }

  //テーブル削除
  private static function DropTable($table) {
    $result = SetupDB::DropTable($table);
    if ($result) unset(self::$table_list[array_search($table, self::$table_list)]);
    self::Output(SetupMessage::DROP_TABLE, $table, $result);
  }

  //カラム削除
  private static function DropColumn($table, $column) {
    $stack = SetupDB::ShowColumn($table);
    if (! in_array($column, $stack)) return true;
    $title = SetupMessage::DROP_COLUMN . $table;
    self::Output($title, $column, SetupDB::DropColumn($table, $column));
  }

  //初期データ登録
  private static function Insert($table) {
    switch ($table) {
    case 'user_entry':
      //管理者登録
      $items  = 'room_no, user_no, uname, handle_name, icon_no, profile, password, role, live';
      $str    = "0, 0, '%s', '%s', 1, '%s', '%s', '%s', '%s'";
      $values = sprintf($str, GM::SYSTEM, Message::SYSTEM, GM::PROFILE, ServerConfig::PASSWORD,
			GM::ROLE, UserLive::LIVE);
      DB::Insert($table, $items, $values);
      break;

    case 'user_icon':
      //アイコン登録
      $items = 'icon_no, icon_name, icon_filename, icon_width, icon_height, color';

      //身代わり君 (No. 0)
      extract(SetupConfig::$dummy_boy_icon); //身代わり君アイコンの設定をロード
      $values = "0, '{$name}', '{$file}', {$width}, {$height}, '{$color}'";
      DB::Insert($table, $items, $values);

      //初期アイコン
      foreach (SetupConfig::$default_icon as $id => $list) {
	extract($list);
	$values = "{$id}, '{$name}', '{$file}', {$width}, {$height}, '{$color}'";
	self::Output(SetupMessage::ICON, $values, DB::Insert($table, $items, $values));
      }
      break;

    case 'count_limit':
      //ロックキー
      foreach (array('room', 'icon') as $value) {
	DB::Insert($table, 'type', "'{$value}'");
      }
      break;
    }
  }

  //更新処理
  private static function Update($table) {
    switch ($table) {
    case 'room':
      if (self::IsRevision(863)) {
	$stack = array('room_no', 'name', 'comment', 'max_user', 'game_option', 'option_role',
		       'date', 'vote_count', 'revote_count', 'winner', 'establisher_ip');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'user_entry':
      if (self::IsRevision(863)) {
	$stack = array('room_no', 'user_no', 'uname', 'handle_name', 'icon_no', 'sex',
		       'password', 'role', 'role_id', 'objection', 'live', 'ip_address');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'player':
      if (self::IsRevision(863)) {
	$stack = array('id', 'room_no', 'date', 'user_no', 'role');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'talk':
      if (self::IsRevision(494)) {
	self::RegenerateIndex($table, $table . '_index', 'room_no, date, scene');
      }

      if (self::IsRevision(863)) {
	self::DropColumn($table, 'objection');

	$stack = array('id', 'room_no', 'date', 'location', 'uname', 'role_id',
		       'action', 'font_type', 'spend_time');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'talk_beforegame':
      if (self::IsRevision(494)) self::RegenerateIndex($table, $table . '_index', 'room_no');

      if (self::IsRevision(863)) {
	$stack = array('id', 'room_no', 'date', 'location', 'uname', 'handle_name',
		       'action', 'font_type', 'spend_time');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'talk_aftergame':
      if (self::IsRevision(494)) self::RegenerateIndex($table, $table . '_index', 'room_no');

      if (self::IsRevision(863)) {
	$stack = array('id', 'room_no', 'date', 'location', 'uname', 'action', 'font_type',
		       'spend_time');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'vote':
      if (self::IsRevision(863)) {
	$stack = array('room_no', 'date', 'type', 'uname', 'user_no', 'target_no',
		       'vote_number', 'vote_count', 'revote_count');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'system_message':
      if (self::IsRevision(863)) {
	$stack = array('room_no', 'date', 'type', 'message');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'result_ability':
      if (self::IsRevision(863)) {
	$stack = array('room_no', 'date', 'type', 'user_no', 'target', 'result');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'result_dead':
      if (self::IsRevision(863)) {
	$stack = array('room_no', 'date', 'type', 'handle_name', 'result');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'result_lastwords':
      if (self::IsRevision(863)) {
	$stack = array('room_no', 'date', 'handle_name');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'result_vote_kill':
      if (self::IsRevision(863)) {
	$stack = array('id', 'room_no', 'date', 'count', 'handle_name', 'target_name',
		       'vote', 'poll');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;

    case 'user_icon':
      if (self::IsRevision(863)) {
	$stack = array('icon_no', 'icon_name', 'icon_filename', 'icon_width', 'icon_height',
		       'color', 'session_id', 'category', 'appearance', 'author');
	foreach ($stack as $column) self::ChangeColumn($table, $column);
      }
      break;
    }
  }

  //再構成処理
  private static function Reset($table) {
    switch ($table) {
    case 'count_limit':
      if (self::IsRevision(863)) self::DropTable($table);
      break;

    case 'document_cache':
      if (self::IsRevision(792) || self::IsRevision(863)) self::DropTable($table);
      break;
    }
  }

  //必要なテーブルがあるか確認する
  private static function CheckTable($name) {
    if (ServerConfig::REVISION >= ScriptInfo::REVISION) {
      Text::p($name, SetupMessage::ALREADY);
      return;
    }
    self::$table_list = SetupDB::ShowTable(); //テーブルのリストをセット

    $stack = array(
      'room', 'user_entry', 'player',
      'talk', 'talk_beforegame', 'talk_aftergame', 'user_talk_count',
      'vote', 'system_message',
      'result_ability', 'result_dead', 'result_lastwords', 'result_vote_kill',
      'user_icon',
      'count_limit', 'document_cache'
    );
    foreach ($stack as $table) {
      if (self::Exists($table)) self::Reset($table);
      if (! self::Exists($table)) {
	self::CreateTable($table);
	self::Insert($table);
      }
      self::Update($table);
    }
    Text::p($name, SetupMessage::FINISHED);
  }

  //結果出力
  private static function Output($title, $name, $result) {
    $str = $result ? SetupMessage::SUCCESS : SetupMessage::FAILED;
    Text::p(sprintf('%s: %s', $name, $str), $title);
  }
}
