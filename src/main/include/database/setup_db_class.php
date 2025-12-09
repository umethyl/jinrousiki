<?php
//-- DB アクセス (データベース初期セットアップ拡張) --//
final class SetupDB {
  //データベース接続 (データベース作成用)
  public static function Connect() {
    try {
      DB::Initialize(sprintf('mysql:host=%s', DatabaseConfig::HOST));
    } catch (PDOException $e) {
      HTML::OutputFooter(true);
    }
  }

  //データベース作成
  public static function CreateDatabase(string $name) {
    $query = 'CREATE DATABASE %s DEFAULT CHARSET %s';
    DB::Prepare(sprintf($query, $name, DatabaseConfig::ENCODE));
    return DB::FetchBool();
  }

  //権限設定
  public static function Grant(string $name) {
    $query = 'GRANT ALL ON %s.* TO %s';
    DB::Prepare(sprintf($query, $name, DatabaseConfig::USER));
    return DB::FetchBool(true);
  }

  //テーブル一覧取得
  public static function ShowTable() {
    DB::Prepare('SHOW TABLES');
    return DB::FetchColumn();
  }

  //テーブル作成
  public static function CreateTable(string $table) {
    $query = 'CREATE TABLE %s(%s) ENGINE = InnoDB';
    DB::Prepare(sprintf($query, $table, self::GetSchema($table)));
    return DB::FetchBool();
  }

  //カラム一覧取得
  public static function ShowColumn(string $table) {
    DB::Prepare('SHOW COLUMNS FROM ' . $table);
    return DB::FetchColumn();
  }

  //テーブル文字コード変更
  public static function ChangeEncode(string $table, string $encode) {
    $query = 'ALTER TABLE %s CONVERT TO CHARACTER SET %s COLLATE %s_general_ci';
    DB::Prepare(sprintf($query, $table, $encode, $encode));
    return DB::FetchBool();
  }

  //型変更
  public static function ChangeColumn(string $table, string $column) {
    $query = 'ALTER TABLE %s CHANGE %s %s';
    DB::Prepare(sprintf($query, $table, $column, self::GetColumn($table, $column)));
    return DB::FetchBool();
  }

  //インデックス再生成
  public static function RegenerateIndex(string $table, string $index, string $value) {
    $query = 'ALTER TABLE %s DROP INDEX %s, ADD INDEX %s (%s)';
    DB::Prepare(sprintf($query, $table, $index, $index, $value));
    return DB::FetchBool();
  }

  //テーブル削除
  public static function DropTable(string $table) {
    DB::Prepare('DROP TABLE ' . $table);
    return DB::FetchBool();
  }

  //カラム削除
  public static function DropColumn(string $table, string $column) {
    $query = 'ALTER TABLE %s DROP %s';
    DB::Prepare(sprintf($query, $table, $column));
    return DB::FetchBool();
  }

  //スキーマ取得
  private static function GetSchema(string $table) {
    switch ($table) {
    case 'room':
      return <<<EOF
room_no            MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY,
name               VARCHAR(512),
comment            VARCHAR(512),
max_user           TINYINT   UNSIGNED,
game_option        VARCHAR(1024),
option_role        VARCHAR(1024),
status             VARCHAR(16),
date               TINYINT   UNSIGNED,
scene              VARCHAR(16),
vote_count         TINYINT   UNSIGNED NOT NULL,
revote_count       TINYINT   UNSIGNED NOT NULL,
scene_start_time   INT(20)            NOT NULL,
last_update_time   INT(20)            NOT NULL,
overtime_alert     BOOLEAN            NOT NULL DEFAULT 0,
winner             VARCHAR(32),
establisher_ip     VARCHAR(40),
establish_datetime DATETIME,
start_datetime     DATETIME,
finish_datetime    DATETIME,
INDEX room_index (status)
EOF;

    case 'user_entry':
      return <<<EOF
room_no         MEDIUMINT UNSIGNED NOT NULL,
user_no         SMALLINT,
uname           VARCHAR(512),
handle_name     VARCHAR(512),
icon_no         MEDIUMINT UNSIGNED,
profile         TEXT,
sex             VARCHAR(16),
password        VARCHAR(48),
role            VARCHAR(2048),
role_id         INT       UNSIGNED,
objection       TINYINT   UNSIGNED NOT NULL,
live            VARCHAR(16),
session_id      CHAR(32) UNIQUE,
last_words      TEXT,
ip_address      VARCHAR(40),
last_load_scene VARCHAR(16),
INDEX user_entry_index (room_no, user_no)
EOF;

    case 'player':
      return <<<EOF
id      INT       UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
room_no MEDIUMINT UNSIGNED NOT NULL,
date    TINYINT   UNSIGNED,
scene   VARCHAR(16),
user_no SMALLINT,
role    VARCHAR(2048),
INDEX player_index (room_no)
EOF;

    case 'talk':
      return <<<EOF
id         INT       UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
room_no    MEDIUMINT UNSIGNED NOT NULL,
date       TINYINT   UNSIGNED,
scene      VARCHAR(16),
location   VARCHAR(32),
uname      VARCHAR(512),
role_id    INT       UNSIGNED,
action     VARCHAR(32),
sentence   TEXT,
font_type  VARCHAR(32),
spend_time SMALLINT  UNSIGNED,
time       INT(20)            NOT NULL,
INDEX talk_index (room_no, date, scene)
EOF;

    case 'talk_beforegame':
      return <<<EOF
id          INT       UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
room_no     MEDIUMINT UNSIGNED NOT NULL,
date        TINYINT   UNSIGNED,
scene       VARCHAR(16),
location    VARCHAR(32),
uname       VARCHAR(512),
handle_name VARCHAR(512),
color       VARCHAR(7),
action      VARCHAR(32),
sentence    TEXT,
font_type   VARCHAR(32),
spend_time  SMALLINT  UNSIGNED,
time        INT(20)            NOT NULL,
INDEX talk_beforegame_index (room_no)
EOF;

    case 'talk_aftergame':
      return <<<EOF
id         INT       UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
room_no    MEDIUMINT UNSIGNED NOT NULL,
date       TINYINT   UNSIGNED,
scene      VARCHAR(16),
location   VARCHAR(32),
uname      VARCHAR(512),
action     VARCHAR(32),
sentence   TEXT,
font_type  VARCHAR(32),
spend_time SMALLINT  UNSIGNED,
time       INT(20)            NOT NULL,
INDEX talk_aftergame_index (room_no)
EOF;

    case 'user_talk_count':
      return <<<EOF
room_no    MEDIUMINT UNSIGNED NOT NULL,
user_no    SMALLINT,
date       TINYINT   UNSIGNED,
talk_count SMALLINT  UNSIGNED,
INDEX user_talk_count_index (room_no, user_no)
EOF;

    case 'vote':
      return <<<EOF
room_no      MEDIUMINT UNSIGNED NOT NULL,
date         TINYINT   UNSIGNED,
scene        VARCHAR(16),
type         VARCHAR(32),
uname        VARCHAR(512),
user_no      SMALLINT,
target_no    VARCHAR(512),
vote_number  SMALLINT  UNSIGNED,
vote_count   TINYINT   UNSIGNED NOT NULL,
revote_count TINYINT   UNSIGNED NOT NULL,
INDEX vote_index (room_no, date, scene, vote_count)
EOF;

    case 'system_message':
      return <<<EOF
room_no MEDIUMINT UNSIGNED NOT NULL,
date    TINYINT   UNSIGNED,
type    VARCHAR(32),
message VARCHAR(64),
INDEX system_message_index (room_no, date, type(10))
EOF;

    case 'result_ability':
      return <<<EOF
room_no MEDIUMINT UNSIGNED NOT NULL,
date    TINYINT   UNSIGNED,
type    VARCHAR(32),
user_no SMALLINT,
target  VARCHAR(512),
result  VARCHAR(64),
INDEX result_ability_index (room_no, date, type(10))
EOF;

    case 'result_dead':
      return <<<EOF
room_no     MEDIUMINT UNSIGNED NOT NULL,
date        TINYINT   UNSIGNED,
scene       VARCHAR(16),
type        VARCHAR(32),
handle_name VARCHAR(512),
result      VARCHAR(64),
INDEX result_dead_index (room_no, date, scene)
EOF;

    case 'result_lastwords':
      return <<<EOF
room_no     MEDIUMINT UNSIGNED NOT NULL,
date        TINYINT UNSIGNED,
handle_name VARCHAR(512),
message     TEXT,
INDEX result_lastwords_index (room_no, date)
EOF;

    case 'result_vote_kill':
      return <<<EOF
id          INT       UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
room_no     MEDIUMINT UNSIGNED NOT NULL,
date        TINYINT   UNSIGNED,
count       TINYINT   UNSIGNED,
handle_name VARCHAR(512),
target_name VARCHAR(512),
vote        SMALLINT  UNSIGNED,
poll        SMALLINT  UNSIGNED,
INDEX result_vote_kill_index (room_no, date, count)
EOF;

    case 'user_icon':
      return <<<EOF
icon_no       MEDIUMINT UNSIGNED PRIMARY KEY,
icon_name     VARCHAR(512),
icon_filename VARCHAR(512),
icon_width    SMALLINT  UNSIGNED,
icon_height   SMALLINT  UNSIGNED,
color         VARCHAR(7),
session_id    CHAR(32),
category      VARCHAR(512),
appearance    VARCHAR(512),
author        VARCHAR(512),
regist_date   DATETIME,
disable       BOOLEAN
EOF;

    case 'count_limit':
      return <<<EOF
type  VARCHAR(16) PRIMARY KEY,
count TINYINT UNSIGNED NOT NULL DEFAULT 0
EOF;

    case 'document_cache':
      return <<<EOF
room_no MEDIUMINT UNSIGNED DEFAULT 0,
name    CHAR(32) NOT NULL,
content MEDIUMBLOB,
expire  INT(20)  NOT NULL,
hash    CHAR(32),
INDEX document_cache_index (room_no, name),
INDEX expire (expire)
EOF;
    }
  }

  //型取得 (変更用)
  private static function GetColumn(string $table, string $column) {
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
      return 'type VARCHAR(32)';

    case 'color':
      return 'color VARCHAR(7)';

    case 'session_id':
      return 'session_id CHAR(32)';

    case 'ip_address':
      return 'ip_address VARCHAR(40)';
    }
  }
}
