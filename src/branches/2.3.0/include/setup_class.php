<?php
//-- データベース初期セットアップクラス --//
class JinrouSetup {
  private static $revision; //バージョン情報
  private static $table_list = array(); //テーブルリスト

  //実行処理
  static function Execute() {
    HTML::OutputHeader(ServerConfig::TITLE . ServerConfig::COMMENT . ' [初期設定]', null, true);
    DB::Check(true, true);
    self::$revision = ServerConfig::REVISION; //前回のパッケージのリビジョン番号を取得

    $name = DatabaseConfig::NAME;
    Text::p($name, '対象データベース');
    if (! DB::ConnectInHeader()) self::CreateDatabase($name);
    self::CheckTable($name);

    foreach (DatabaseConfig::$name_list as $id => $name) {
      DB::Disconnect();
      Text::p($name, '対象データベース');
      if (! DB::ConnectInHeader($id + 1)) self::CreateDatabase($name);
      self::CheckTable($name);
    }

    Text::p('初期設定の処理が終了しました');
    HTML::OutputFooter();
  }

  //テーブル存在確認
  private static function Exists($table) { return in_array($table, self::$table_list); }

  //対象バージョン確認
  private static function IsRevision($revision) {
    return 0 < self::$revision && self::$revision <= $revision;
  }

  //データベース作成
  private static function CreateDatabase($name) {
    SetupDB::Connect();
    $result = SetupDB::CreateDatabase($name);
    if ($result) SetupDB::Grant($name);
    self::Output('データベース作成', $name, $result);
    DB::Reconnect($name);
  }

  //テーブル作成
  private static function CreateTable($table) {
    self::Output('テーブル作成', $table, SetupDB::CreateTable($table));
  }

  //インデックス作成
  private static function CreateIndex($table, $index, $value) {
    self::Output('インデックス生成', $table, SetupDB::CreateIndex($table, $index, $value));
  }

  //インデックス再生成
  private static function RegenerateIndex($table, $index, $value) {
    self::Output('インデックス再生成', $table, SetupDB::RegenerateIndex($table, $index, $value));
  }

  //型変更
  private static function ChangeColumn($table, $column) {
    self::Output('カラム変更: ' . $table, $column, SetupDB::ChangeColumn($table, $column));
  }

  //テーブル削除
  private static function DropTable($table) {
    $result = SetupDB::DropTable($table);
    if ($result) unset(self::$table_list[array_search($table, self::$table_list)]);
    self::Output('テーブル削除', $table, $result);
  }

  //カラム削除
  private static function DropColumn($table, $column) {
    $stack = DB::FetchColumn('SHOW COLUMNS FROM ' . $table);
    if (! in_array($column, $stack)) return true;
    self::Output('カラム削除: ' . $table, $column, SetupDB::DropColumn($table, $column));
  }

  //初期データ登録
  private static function Insert($table) {
    switch ($table) {
    case 'user_entry':
      //管理者登録
      $items  = 'room_no, user_no, uname, handle_name, icon_no, profile, password, role, live';
      $str    = "0, 0, 'system', 'システム', 1, 'ゲームマスター', '%s', 'GM', 'live'";
      $values = sprintf($str, ServerConfig::PASSWORD);
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
	self::Output('ユーザアイコン登録', $values, DB::Insert($table, $items, $values));
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
    if (self::$revision >= ScriptInfo::REVISION) {
      Text::p($name, '設定完了済み');
      return;
    }
    self::$table_list = DB::FetchColumn('SHOW TABLES'); //テーブルのリストをセット

    $stack = array(
      'room', 'user_entry', 'player',
      'talk', 'talk_beforegame', 'talk_aftergame',
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
    Text::p($name, '設定完了');
  }

  //結果出力
  private static function Output($title, $name, $result) {
    Text::p(sprintf('%s: %s', $name, $result ? '成功' : '失敗'), $title);
  }
}

//-- DB アクセス (データベース初期セットアップ拡張) --//
class SetupDB {
  //データベース接続 (データベース作成用)
  static function Connect() {
    try {
      DB::Initialize(sprintf('mysql:host=%s', DatabaseConfig::HOST));
    }
    catch (PDOException $e) {
      HTML::OutputFooter(true);
    }
  }

  //権限設定
  static function Grant($name) {
    return DB::FetchBool(sprintf('GRANT ALL ON %s.* TO %s', $name, DatabaseConfig::USER), true);
  }

  //データベース作成
  static function CreateDatabase($name) {
    return DB::FetchBool(sprintf('CREATE DATABASE %s DEFAULT CHARSET utf8', $name));
  }

  //テーブル作成
  static function CreateTable($table) {
    $schema = self::GetSchema($table);
    return DB::FetchBool(sprintf('CREATE TABLE %s(%s) ENGINE = InnoDB', $table, $schema));
  }

  //型変更
  static function ChangeColumn($table, $column) {
    $schema = self::GetColumn($table, $column);
    return DB::FetchBool(sprintf('ALTER TABLE %s CHANGE %s %s', $table, $column, $schema));
  }

  //インデックス再生成
  static function RegenerateIndex($table, $index, $value) {
    $query  = 'ALTER TABLE %s DROP INDEX %s, ADD INDEX %s (%s)';
    return DB::FetchBool(sprintf($query, $table, $index, $index, $value));
  }

  //テーブル削除
  static function DropTable($table) {
    return DB::FetchBool(sprintf('DROP TABLE %s', $table));
  }

  //カラム削除
  static function DropColumn($table, $column) {
    return DB::FetchBool(sprintf('ALTER TABLE %s DROP %s', $table, $column));
  }

  //スキーマ取得
  private static function GetSchema($table) {
    switch ($table) {
    case 'room':
      return <<<EOF
room_no MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY, name VARCHAR(512), comment VARCHAR(512),
  max_user TINYINT UNSIGNED, game_option VARCHAR(1024), option_role VARCHAR(1024),
  status VARCHAR(16), date TINYINT UNSIGNED, scene VARCHAR(16),
  vote_count TINYINT UNSIGNED NOT NULL, revote_count TINYINT UNSIGNED NOT NULL,
  scene_start_time INT(20) NOT NULL, last_update_time INT(20) NOT NULL,
  overtime_alert BOOLEAN NOT NULL DEFAULT 0, winner VARCHAR(32), establisher_ip VARCHAR(40),
  establish_datetime DATETIME, start_datetime DATETIME, finish_datetime DATETIME,
INDEX room_index(status)
EOF;

    case 'user_entry':
      return <<<EOF
room_no MEDIUMINT UNSIGNED NOT NULL, user_no SMALLINT, uname VARCHAR(512), handle_name VARCHAR(512),
  icon_no MEDIUMINT UNSIGNED, profile TEXT, sex VARCHAR(16), password VARCHAR(48),
  role VARCHAR(2048), role_id INT UNSIGNED, objection TINYINT UNSIGNED NOT NULL,
  live VARCHAR(16), session_id CHAR(32) UNIQUE, last_words TEXT, ip_address VARCHAR(40),
  last_load_scene VARCHAR(16),
INDEX user_entry_index(room_no, user_no)
EOF;

    case 'player':
      return <<<EOF
id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no MEDIUMINT UNSIGNED NOT NULL,
  date TINYINT UNSIGNED, scene VARCHAR(16), user_no SMALLINT, role VARCHAR(2048),
INDEX player_index(room_no)
EOF;

    case 'talk':
      return <<<EOF
id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no MEDIUMINT UNSIGNED NOT NULL,
  date TINYINT UNSIGNED, scene VARCHAR(16), location VARCHAR(32), uname VARCHAR(512),
  role_id INT UNSIGNED, action VARCHAR(32), sentence TEXT, font_type VARCHAR(32),
  spend_time SMALLINT UNSIGNED, time INT(20) NOT NULL,
INDEX talk_index (room_no, date, scene)
EOF;

    case 'talk_beforegame':
      return <<<EOF
id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no MEDIUMINT UNSIGNED NOT NULL,
  date TINYINT UNSIGNED, scene VARCHAR(16), location VARCHAR(32), uname VARCHAR(512),
  handle_name VARCHAR(512), color VARCHAR(7), action VARCHAR(32), sentence TEXT,
  font_type VARCHAR(32), spend_time SMALLINT UNSIGNED, time INT(20) NOT NULL,
INDEX talk_beforegame_index(room_no)
EOF;

    case 'talk_aftergame':
      return <<<EOF
id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no MEDIUMINT UNSIGNED NOT NULL,
  date TINYINT UNSIGNED, scene VARCHAR(16), location VARCHAR(32), uname VARCHAR(512),
  action VARCHAR(32), sentence TEXT, font_type VARCHAR(32), spend_time SMALLINT UNSIGNED,
  time INT(20) NOT NULL,
INDEX talk_aftergame_index(room_no)
EOF;

    case 'vote':
      return <<<EOF
room_no MEDIUMINT UNSIGNED NOT NULL, date TINYINT UNSIGNED, scene VARCHAR(16), type VARCHAR(32),
  uname VARCHAR(512), user_no SMALLINT, target_no VARCHAR(512), vote_number SMALLINT UNSIGNED,
  vote_count TINYINT UNSIGNED NOT NULL, revote_count TINYINT UNSIGNED NOT NULL,
INDEX vote_index(room_no, date, scene, vote_count)
EOF;

    case 'system_message':
      return <<<EOF
room_no MEDIUMINT UNSIGNED NOT NULL, date TINYINT UNSIGNED, type VARCHAR(32), message VARCHAR(64),
INDEX system_message_index(room_no, date, type(10))
EOF;

    case 'result_ability':
      return <<<EOF
room_no MEDIUMINT UNSIGNED NOT NULL, date TINYINT UNSIGNED, type VARCHAR(32), user_no SMALLINT,
  target VARCHAR(512), result VARCHAR(64),
INDEX result_ability_index(room_no, date, type(10))
EOF;

    case 'result_dead':
      return <<<EOF
room_no MEDIUMINT UNSIGNED NOT NULL, date TINYINT UNSIGNED, scene VARCHAR(16), type VARCHAR(32),
  handle_name VARCHAR(512), result VARCHAR(64),
INDEX result_dead_index(room_no, date, scene)
EOF;

    case 'result_lastwords':
      return <<<EOF
room_no MEDIUMINT UNSIGNED NOT NULL, date TINYINT UNSIGNED, handle_name VARCHAR(512), message TEXT,
INDEX result_lastwords_index(room_no, date)
EOF;

    case 'result_vote_kill':
      return <<<EOF
id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no MEDIUMINT UNSIGNED NOT NULL,
  date TINYINT UNSIGNED, count TINYINT UNSIGNED, handle_name VARCHAR(512), target_name VARCHAR(512),
  vote SMALLINT UNSIGNED, poll SMALLINT UNSIGNED,
INDEX result_vote_kill_index(room_no, date, count)
EOF;

    case 'user_icon':
      return <<<EOF
icon_no MEDIUMINT UNSIGNED PRIMARY KEY, icon_name VARCHAR(512), icon_filename VARCHAR(512),
  icon_width SMALLINT UNSIGNED, icon_height SMALLINT UNSIGNED, color VARCHAR(7),
  session_id CHAR(32), category VARCHAR(512), appearance VARCHAR(512), author VARCHAR(512),
  regist_date DATETIME, disable BOOLEAN
EOF;

    case 'count_limit':
      return <<<EOF
type VARCHAR(16) PRIMARY KEY, count TINYINT UNSIGNED NOT NULL DEFAULT 0
EOF;

    case 'document_cache':
      return <<<EOF
room_no MEDIUMINT UNSIGNED DEFAULT 0, name CHAR(32) NOT NULL, content MEDIUMBLOB,
  expire INT(20) NOT NULL, hash CHAR(32),
INDEX document_cache_index(room_no, name),
INDEX expire(expire)
EOF;
    }
  }

  //型取得 (変更用)
  private static function GetColumn($table, $column) {
    switch ($table) {
    case 'room':
      switch ($column) {
      case 'name':
      case 'comment':
	return $column . ' VARCHAR(512)';

      case 'max_user':
	return 'max_user TINYINT UNSIGNED';

      case 'game_option':
      case 'option_role':
	return $column . ' VARCHAR(1024)';

      case 'winner':
	return 'winner VARCHAR(32)';

      case 'establisher_ip':
	return 'establisher_ip VARCHAR(40)';
      }
      break;

    case 'user_entry':
      switch ($column) {
      case 'sex':
      case 'live':
	return $column . ' VARCHAR(16)';

      case 'password':
	return 'password VARCHAR(48)';

      case 'objection':
	return 'objection TINYINT UNSIGNED NOT NULL';
      }
      break;

    case 'talk':
    case 'talk_beforegame':
    case 'talk_aftergame':
      switch ($column) {
      case 'location':
      case 'action':
      case 'font_type':
	return $column . ' VARCHAR(32)';

      case 'spend_time':
	return 'spend_time SMALLINT UNSIGNED';
      }
      break;

    case 'vote':
      switch ($column) {
      case 'target_no':
	return 'target_no VARCHAR(512)';

      case 'vote_number':
	return 'vote_number SMALLINT UNSIGNED';
      }
      break;

    case 'system_message':
      switch ($column) {
      case 'message':
	return 'message VARCHAR(64)';
      }
      break;

    case 'result_ability':
    case 'result_dead':
      switch ($column) {
      case 'target':
	return 'target VARCHAR(512)';

      case 'result':
	return 'result VARCHAR(64)';
      }
      break;

    case 'result_vote_kill':
      switch ($column) {
      case 'count':
	return 'count TINYINT UNSIGNED';

      case 'target_name':
	return 'target_name VARCHAR(512)';

      case 'vote':
      case 'poll':
	return $column . ' SMALLINT UNSIGNED';
      }
      break;

    case 'user_icon':
      switch ($column) {
      case 'icon_name':
      case 'icon_filename':
      case 'category':
      case 'appearance':
      case 'author':
	return $column . ' VARCHAR(512)';

      case 'icon_width':
      case 'icon_height':
	return $column . ' SMALLINT UNSIGNED';
      }
      break;
    }

    //共通
    switch ($column) {
    case 'id':
      return 'id INT UNSIGNED NOT NULL AUTO_INCREMENT';

    case 'room_no':
      return 'room_no MEDIUMINT UNSIGNED NOT NULL';

    case 'date':
      return 'date TINYINT UNSIGNED';

    case 'user_no':
      return 'user_no SMALLINT';

    case 'uname':
    case 'handle_name':
      return $column . ' VARCHAR(512)';

    case 'role':
      return 'role VARCHAR(2048)';

    case 'role_id':
      return 'role_id INT UNSIGNED';

    case 'icon_no':
      return 'icon_no MEDIUMINT UNSIGNED';

    case 'vote_count':
    case 'revote_count':
      return $column . ' TINYINT UNSIGNED NOT NULL';

    case 'type':
      return $column . ' VARCHAR(32)';

    case 'color':
      return 'color VARCHAR(7)';

    case 'session_id':
      return 'session_id CHAR(32)';

    case 'ip_address':
      return 'ip_address VARCHAR(40)';
    }
  }
}
