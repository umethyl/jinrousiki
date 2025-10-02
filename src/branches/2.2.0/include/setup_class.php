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

  //インデックス再生成
  private static function CreateIndex($table, $index, $value) {
    self::Output('インデックス再生成', $table, SetupDB::CreateIndex($table, $index, $value));
  }

  //テーブル削除
  private static function DropTable($table) {
    $result = SetupDB::DropTable($table);
    if ($result) unset(self::$table_list[array_search($table, self::$table_list)]);
    self::Output('テーブル削除', $table, $result);
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
	DB::Insert($table, 'count, type', "0, '{$value}'");
      }
      break;
    }
  }

  //更新処理
  private static function Update($table) {
    switch ($table) {
    case 'talk':
      if (self::IsRevision(494)) {
	self::CreateIndex($table, $table . '_index', 'room_no, date, scene');
      }
      break;

    case 'talk_beforegame':
      if (self::IsRevision(494)) self::CreateIndex($table, $table . '_index', 'room_no');
      break;

    case 'talk_aftergame':
      if (self::IsRevision(494)) self::CreateIndex($table, $table . '_index', 'room_no');
      break;
    }
  }

  //再構成処理
  private static function Reset($table) {
    switch ($table) {
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

  //インデックス再生成
  static function CreateIndex($table, $index, $value) {
    $query  = 'ALTER TABLE %s DROP INDEX %s, ADD INDEX %s (%s)';
    return DB::FetchBool(sprintf($query, $table, $index, $index, $value));
  }

  //テーブル削除
  static function DropTable($table) {
    return DB::FetchBool(sprintf('DROP TABLE %s', $table));
  }

  //スキーマ取得
  private static function GetSchema($table) {
    switch ($table) {
    case 'room':
      return <<<EOF
room_no INT NOT NULL PRIMARY KEY, name TEXT, comment TEXT, max_user INT, game_option TEXT,
option_role TEXT, status VARCHAR(16), date INT, scene VARCHAR(16), vote_count INT NOT NULL,
revote_count INT NOT NULL, scene_start_time INT(20) NOT NULL, last_update_time INT(20) NOT NULL,
overtime_alert BOOLEAN NOT NULL DEFAULT 0, winner TEXT, establisher_ip TEXT,
establish_datetime DATETIME, start_datetime DATETIME, finish_datetime DATETIME,
INDEX room_index(status)
EOF;

    case 'user_entry':
      return <<<EOF
room_no INT NOT NULL, user_no INT, uname TEXT, handle_name TEXT, icon_no INT, profile TEXT,
sex TEXT, password TEXT, role TEXT, role_id INT, objection INT NOT NULL, live TEXT,
session_id CHAR(32) UNIQUE, last_words TEXT, ip_address TEXT, last_load_scene VARCHAR(16),
INDEX user_entry_index(room_no, user_no)
EOF;

    case 'player':
      return <<<EOF
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no INT NOT NULL, date INT, scene VARCHAR(16),
user_no INT, role TEXT, INDEX player_index(room_no)
EOF;

    case 'talk':
      return <<<EOF
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no INT NOT NULL, date INT, scene VARCHAR(16),
location TEXT, uname TEXT, role_id INT, action TEXT, sentence TEXT, font_type TEXT, spend_time INT,
time INT(20) NOT NULL,
INDEX talk_index (room_no, date, scene)
EOF;

    case 'talk_beforegame':
      return <<<EOF
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no INT NOT NULL, date INT, scene VARCHAR(16),
location TEXT, uname TEXT, handle_name TEXT, color VARCHAR(7), action TEXT, sentence TEXT,
font_type TEXT, spend_time INT, time INT(20) NOT NULL,
INDEX talk_beforegame_index(room_no)
EOF;

    case 'talk_aftergame':
      return <<<EOF
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no INT NOT NULL, date INT, scene VARCHAR(16),
location TEXT, uname TEXT, action TEXT, sentence TEXT, font_type TEXT, spend_time INT,
time INT(20) NOT NULL,
INDEX talk_aftergame_index(room_no)
EOF;
    case 'vote':
      return <<<EOF
room_no INT NOT NULL, date INT, scene VARCHAR(16), type TEXT, uname TEXT, user_no INT,
target_no TEXT, vote_number INT, vote_count INT NOT NULL, revote_count INT NOT NULL,
INDEX vote_index(room_no, date, scene, vote_count)
EOF;

    case 'system_message':
      return <<<EOF
room_no INT NOT NULL, date INT, type TEXT, message TEXT,
INDEX system_message_index(room_no, date, type(10))
EOF;

    case 'result_ability':
      return <<<EOF
room_no INT NOT NULL, date INT, type TEXT, user_no INT, target TEXT, result TEXT,
INDEX result_ability_index(room_no, date, type(10))
EOF;

    case 'result_dead':
      return <<<EOF
room_no INT NOT NULL, date INT, scene VARCHAR(16), type TEXT, handle_name TEXT, result TEXT,
INDEX result_dead_index(room_no, date, scene)
EOF;

    case 'result_lastwords':
      return <<<EOF
room_no INT NOT NULL, date INT, handle_name TEXT, message TEXT,
INDEX result_lastwords_index(room_no, date)
EOF;

    case 'result_vote_kill':
      return <<<EOF
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no INT NOT NULL, date INT, count INT,
handle_name TEXT, target_name TEXT, vote INT, poll INT,
INDEX result_vote_kill_index(room_no, date, count)
EOF;

    case 'user_icon':
      return <<<EOF
icon_no INT PRIMARY KEY, icon_name TEXT, icon_filename TEXT, icon_width INT, icon_height INT,
color TEXT, session_id TEXT, category TEXT, appearance TEXT, author TEXT, regist_date DATETIME,
disable BOOL
EOF;

    case 'count_limit':
      return 'count INT NOT NULL, type VARCHAR(16)';

    case 'document_cache':
      return <<<EOF
room_no INT DEFAULT 0, name CHAR(32) NOT NULL, content MEDIUMBLOB,
  expire INT NOT NULL, hash CHAR(32),
INDEX document_cache_index(room_no, name),
INDEX expire(expire)
EOF;
    }
  }
}
