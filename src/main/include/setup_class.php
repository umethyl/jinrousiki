<?php
//-- DB アクセス (データベース初期セットアップ拡張) --//
class SetupDB {
  //画面出力
  static function Output() {
    HTML::OutputHeader(ServerConfig::TITLE . ServerConfig::COMMENT . ' [初期設定]', null, true);
    if (! DB::ConnectInHeader()) self::CreateDatabase();
    self::CheckTable();
    HTML::OutputFooter();
  }

  //データベース作成
  private function CreateDatabase() {
    $name   = DatabaseConfig::NAME;
    $result = DB::FetchBool(sprintf('CREATE DATABASE %s DEFAULT CHARSET utf8', $name));
    printf("データベース作成: %s: %s<br>\n", $name, $result ? '成功' : '失敗');
    DB::Reconnect();
  }

  //テーブル作成
  private function CreateTable($table, $query) {
    $result = DB::FetchBool(sprintf('CREATE TABLE %s(%s) ENGINE = InnoDB', $table, $query));
    printf("テーブル作成: %s: %s<br>\n", $table, $result ? '成功' : '失敗');
  }

  //インデックス再生成
  private function CreateIndex($table, $index, $value) {
    $query  = 'ALTER TABLE %s DROP INDEX %s, ADD INDEX %s (%s)';
    $result = DB::FetchBool(sprintf($query, $table, $index, $index, $value));
    printf("インデックス再生成: %s (%s): %s <br>\n", $index, $table, $result ? '成功' : '失敗');
  }

  //必要なテーブルがあるか確認する
  private function CheckTable() {
    $revision = ServerConfig::REVISION; //前回のパッケージのリビジョン番号を取得
    if ($revision >= ScriptInfo::REVISION) {
      echo '初期設定はすでに完了しています';
      return;
    }
    $table_list = DB::FetchArray('SHOW TABLES'); //テーブルのリストを取得

    //チェックしてテーブルが存在しなければ作成する
    $footer  = "<br>\n";

    $table = 'room';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
room_no INT NOT NULL PRIMARY KEY, name TEXT, comment TEXT, max_user INT, game_option TEXT,
option_role TEXT, status VARCHAR(16), date INT, scene VARCHAR(16), vote_count INT NOT NULL,
revote_count INT NOT NULL, scene_start_time INT(20) NOT NULL, last_update_time INT(20) NOT NULL,
overtime_alert BOOLEAN NOT NULL DEFAULT 0, winner TEXT, establisher_ip TEXT,
establish_datetime DATETIME, start_datetime DATETIME, finish_datetime DATETIME,
INDEX room_index(status)
EOF;
      self::CreateTable($table, $query);
    }

    $table = 'user_entry';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
room_no INT NOT NULL, user_no INT, uname TEXT, handle_name TEXT, icon_no INT, profile TEXT,
sex TEXT, password TEXT, role TEXT, role_id INT, objection INT NOT NULL, live TEXT,
session_id CHAR(32) UNIQUE, last_words TEXT, ip_address TEXT, last_load_scene VARCHAR(16),
INDEX user_entry_index(room_no, user_no)
EOF;
      self::CreateTable($table, $query);

      //管理者を登録
      $items  = 'room_no, user_no, uname, handle_name, icon_no, profile, password, role, live';
      $str    = "0, 0, 'system', 'システム', 1, 'ゲームマスター', '%s', 'GM', 'live'";
      $values = sprintf($str, ServerConfig::PASSWORD);
      DB::Insert($table, $items, $values);
    }

    $table = 'player';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no INT NOT NULL, date INT, scene VARCHAR(16),
user_no INT, role TEXT, INDEX player_index(room_no)
EOF;
      self::CreateTable($table, $query);
    }

    $table = 'talk';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no INT NOT NULL, date INT, scene VARCHAR(16),
location TEXT, uname TEXT, role_id INT, objection INT NOT NULL, action TEXT, sentence TEXT,
font_type TEXT, spend_time INT, time INT(20) NOT NULL,
INDEX talk_index (room_no, date, scene)
EOF;
      self::CreateTable($table, $query);
    }
    if (0 < $revision && $revision < 494) {
      self::CreateIndex($table, 'talk_index', 'room_no, date, scene');
    }

    $table = 'talk_beforegame';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no INT NOT NULL, date INT, scene VARCHAR(16),
location TEXT, uname TEXT, handle_name TEXT, color VARCHAR(7), action TEXT, sentence TEXT,
font_type TEXT, spend_time INT, time INT(20) NOT NULL,
INDEX talk_beforegame_index(room_no)
EOF;
      self::CreateTable($table, $query);
    }
    if (0 < $revision && $revision < 494) {
      self::CreateIndex($table, 'talk_beforegame_index', 'room_no');
    }

    $table = 'talk_aftergame';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no INT NOT NULL, date INT, scene VARCHAR(16),
location TEXT, uname TEXT, action TEXT, sentence TEXT, font_type TEXT, spend_time INT,
time INT(20) NOT NULL,
INDEX talk_aftergame_index(room_no)
EOF;
      self::CreateTable($table, $query);
    }
    if (0 < $revision && $revision < 494) {
      self::CreateIndex($table, 'talk_aftergame_index', 'room_no');
    }

    $table = 'vote';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
room_no INT NOT NULL, date INT, scene VARCHAR(16), type TEXT, uname TEXT, user_no INT,
target_no TEXT, vote_number INT, vote_count INT NOT NULL, revote_count INT NOT NULL,
INDEX vote_index(room_no, date, scene, vote_count)
EOF;
      self::CreateTable($table, $query);
    }

    $table = 'system_message';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
room_no INT NOT NULL, date INT, type TEXT, message TEXT,
INDEX system_message_index(room_no, date, type(10))
EOF;
      self::CreateTable($table, $query);
    }

    $table = 'result_ability';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
room_no INT NOT NULL, date INT, type TEXT, user_no INT, target TEXT, result TEXT,
INDEX result_ability_index(room_no, date, type(10))
EOF;
      self::CreateTable($table, $query);
    }

    $table = 'result_dead';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
room_no INT NOT NULL, date INT, scene VARCHAR(16), type TEXT, handle_name TEXT, result TEXT,
INDEX result_dead_index(room_no, date, scene)
EOF;
      self::CreateTable($table, $query);
    }

    $table = 'result_lastwords';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
room_no INT NOT NULL, date INT, handle_name TEXT, message TEXT,
INDEX result_lastwords_index(room_no, date)
EOF;
      self::CreateTable($table, $query);
    }

    $table = 'result_vote_kill';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no INT NOT NULL, date INT, count INT,
handle_name TEXT, target_name TEXT, vote INT, poll INT,
INDEX result_vote_kill_index(room_no, date, count)
EOF;
      self::CreateTable($table, $query);
    }

    $table = 'user_icon';
    if (! in_array($table, $table_list)) {
      $query = <<<EOF
icon_no INT PRIMARY KEY, icon_name TEXT, icon_filename TEXT, icon_width INT, icon_height INT,
color TEXT, session_id TEXT, category TEXT, appearance TEXT, author TEXT, regist_date DATETIME,
disable BOOL
EOF;
      self::CreateTable($table, $query);

      //アイコン登録
      $items = 'icon_no, icon_name, icon_filename, icon_width, icon_height, color';

      //身代わり君のアイコンを登録 (No. 0)
      extract(SetupConfig::$dummy_boy_icon); //身代わり君アイコンの設定をロード
      $values = "0, '{$name}', '{$file}', {$width}, {$height}, '{$color}'";
      DB::Insert($table, $items, $values);

      //初期アイコン登録
      foreach (SetupConfig::$default_icon as $id => $list) {
	extract($list);
	$values = "{$id}, '{$name}', '{$file}', {$width}, {$height}, '{$color}'";
	$result = DB::Insert($table, $items, $values);
	printf("ユーザアイコン登録: %s: %s<br>\n" , $values, $result ? '成功' : '失敗');
      }
    }

    $table = 'count_limit';
    if (! in_array($table, $table_list)) {
      self::CreateTable($table, 'count INT NOT NULL, type VARCHAR(16)');
      foreach (array('room', 'icon') as $value) {
	DB::Insert($table, 'count, type', "0, '{$value}'");
      }
    }

    $query = sprintf('GRANT ALL ON %s.* TO %s', DatabaseConfig::NAME, DatabaseConfig::USER);
    DB::FetchBool($query, true);
    echo "初期設定の処理が終了しました<br>\n";
  }
}
