<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');
$INIT_CONF->LoadClass('SCRIPT_INFO');

OutputHTMLHeader($SERVER_CONF->title . $SERVER_CONF->comment . ' [初期設定]'); //HTMLヘッダ

if(! $DB_CONF->Connect(true, false)){ //DB 接続
  mysql_query("CREATE DATABASE {$DB_CONF->name} DEFAULT CHARSET utf8");
  echo "データベース {$DB_CONF->name} を作成しました。<br>";
  $DB_CONF->Connect(true); //改めて DB 接続
}
echo "</head><body>\n";

CheckTable(); //テーブル作成
OutputHTMLFooter(); //HTMLフッタ

//-- クラス定義 --//
//ユーザアイコンの初期設定
//アイコンイメージをPHP設置時に追加する場合はここの設定も追加してください
class DefaultIcon{
  //アイコンデータ
  public $data = array(
     1 => array('name' => '明灰',     'file' => '001.gif', 'width' => 32, 'height' => 32,
	       'color' => '#DDDDDD'),
     2 => array('name' => '暗灰',     'file' => '002.gif', 'width' => 32, 'height' => 32,
	       'color' => '#999999'),
     3 => array('name' => '黄色',     'file' => '003.gif', 'width' => 32, 'height' => 32,
	       'color' => '#FFD700'),
     4 => array('name' => 'オレンジ', 'file' => '004.gif', 'width' => 32, 'height' => 32,
	       'color' => '#FF9900'),
     5 => array('name' => '赤',       'file' => '005.gif', 'width' => 32, 'height' => 32,
	       'color' => '#FF0000'),
     6 => array('name' => '水色',     'file' => '006.gif', 'width' => 32, 'height' => 32,
	       'color' => '#99CCFF'),
     7 => array('name' => '青',       'file' => '007.gif', 'width' => 32, 'height' => 32,
	       'color' => '#0066FF'),
     8 => array('name' => '緑',       'file' => '008.gif', 'width' => 32, 'height' => 32,
	       'color' => '#00EE00'),
     9 => array('name' => '紫',       'file' => '009.gif', 'width' => 32, 'height' => 32,
	       'color' => '#CC00CC'),
    10 => array('name' => 'さくら色', 'file' => '010.gif', 'width' => 32, 'height' => 32,
	       'color' => '#FF9999'));
}

//身代わり君アイコン
class DummyBoyIcon{
  public $path   = '../img/dummy_boy_user_icon.jpg'; //IconConfig->path からの相対パス
  public $name   = '身代わり君用'; //名前
  public $color  = '#000000'; //色
  public $width  = 45; //幅
  public $height = 45; //高さ
}

//-- 関数 --//
//必要なテーブルがあるか確認する
function CheckTable(){
  global $SERVER_CONF, $DB_CONF, $SCRIPT_INFO;

  //前回のパッケージのリビジョン番号を取得
  $revision = $SERVER_CONF->last_updated_revision;
  if($revision >= $SCRIPT_INFO->revision){
    echo '初期設定はすでに完了しています。';
    return;
  }
  $table_list = FetchArray('SHOW TABLES'); //テーブルのリストを取得

  //チェックしてテーブルが存在しなければ作成する
  $header  = 'テーブル';
  $footer  = '<br>'."\n";
  $str     = 'を作成しました' . $footer;
  $success = ') を追加しました';
  $failed  = ') を追加できませんでした';

  $table = 'room';
  $title = $header . ' (' . $table . ') ';
  if(! in_array($table, $table_list)){
    $query = <<<EOF
room_no INT NOT NULL PRIMARY KEY, room_name TEXT, room_comment TEXT, max_user INT, game_option TEXT,
option_role TEXT, status TEXT, date INT, day_night TEXT, last_updated TEXT, victory_role TEXT,
establisher_ip TEXT, establish_time DATETIME, start_time DATETIME, finish_time DATETIME
EOF;
    SendQuery("CREATE TABLE {$table}({$query})");
    echo $title . $str;
  }
  elseif($revision > 0){
    //追加フィールド処理
    $column = FetchArray('SHOW COLUMNS FROM ' . $table);
    $stack  = array(
      'establisher_ip' => 'TEXT',
      'establish_time' => 'DATETIME',
      'start_time'     => 'DATETIME',
      'finish_time'    => 'DATETIME');
    foreach($stack as $name => $type){
      if(in_array($name, $column)) continue;
      $status = SendQuery("ALTER TABLE {$table} ADD {$name} {$type}") ? $success : $failed;
      echo $title . 'にフィールド (' . $name . $status . $footer;
    }
  }

  $table = 'user_entry';
  $title = $header . ' (' . $table . ') ';
  if(! in_array($table, $table_list)){
    $query = <<<EOF
room_no INT NOT NULL, user_no INT, uname TEXT, handle_name TEXT, icon_no INT, profile TEXT,
sex TEXT, password TEXT, role TEXT, live TEXT, session_id CHAR(32) UNIQUE, last_words TEXT,
ip_address TEXT, last_load_day_night TEXT, INDEX user_entry_index(room_no, user_no)
EOF;
    SendQuery("CREATE TABLE {$table}({$query})");
    echo $title . $str;

    //管理者を登録
    SendQuery("INSERT INTO {$table}
		(room_no, user_no, uname, handle_name, icon_no, profile, password, role, live)
		VALUES(0, 0, 'system', 'システム', 1, 'ゲームマスター',
		'{$SERVER_CONF->system_password}', 'GM', 'live')");
  }
  elseif(0 < $revision && $revision < 152){
    SendQuery("ALTER TABLE {$table} MODIFY room_no INT NOT NULL"); //room_no の型を変更
    echo $header . ' (' . $table . ') の room_no の型を "INT NOT NULL" に変更しました' . $footer;

    if($revision < 140){ //INDEX を設定
      SendQuery("ALTER TABLE {$table} ADD INDEX user_entry_index(room_no, user_no)");
      echo $title . 'に INDEX (room_no, user_no) を設定しました' . $footer;
    }
  }

  $table = 'talk';
  $title = $header . ' (' . $table . ') ';
  if(! in_array($table, $table_list)){
    $query = <<<EOF
talk_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, room_no INT NOT NULL, date INT, location TEXT,
uname TEXT, time INT NOT NULL, sentence TEXT, font_type TEXT, spend_time INT,
INDEX talk_index(room_no, date, time)
EOF;
    SendQuery("CREATE TABLE {$table}({$query})");
    echo $title . $str;
  }
  elseif($revision > 0){
    //追加フィールド処理
    $column = FetchArray('SHOW COLUMNS FROM ' . $table);
    $stack  = array('talk_id' => 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY');
    foreach($stack as $name => $type){
      if(in_array($name, $column)) continue;
      $status = SendQuery("ALTER TABLE room ADD $name $type") ? $success : $failed;
      echo $title . 'にフィールド (' . $name . $status . $footer;
    }

    if($revision < 152){
      SendQuery("ALTER TABLE {$table} MODIFY room_no INT NOT NULL"); //room_no の型を変更
      echo $title . 'の room_no の型を "INT NOT NULL" に変更しました' . $footer;

      if($revision < 140){ //time の型を変更、INDEX を設定
	SendQuery("ALTER TABLE {$table} MODIFY time INT NOT NULL");
	echo $title . 'の time の型を "INT NOT NULL" に変更しました' . $footer;

	SendQuery("ALTER TABLE {$table} ADD INDEX talk_index(room_no, date, time)");
	echo $title . 'に INDEX (room_no, date, time) を設定しました' . $footer;
      }
    }
  }

  $table = 'vote';
  $title = $header . ' (' . $table . ') ';
  if(! in_array($table, $table_list)){
    $query = <<<EOF
room_no INT NOT NULL, date INT, uname TEXT, target_uname TEXT, vote_number INT, vote_times INT,
situation TEXT, INDEX vote_index(room_no, date)
EOF;
    SendQuery("CREATE TABLE {$table}({$query})");
    echo $title . $str;
  }
  elseif(0 < $revision && $revision < 152){
    SendQuery("ALTER TABLE {$table} MODIFY room_no INT NOT NULL"); //room_no の型を変更
    echo $title . 'の room_no の型を "INT NOT NULL" に変更しました' . $footer;

    if($revision < 140){ //INDEX を設定
      SendQuery("ALTER TABLE {$table} ADD INDEX vote_index(room_no, date)");
      echo $title . 'に INDEX (room_no, date) を設定しました' . $footer;
    }
  }

  $table = 'system_message';
  $title = $header . ' (' . $table . ') ';
  if(! in_array($table, $table_list)){
    $query = <<<EOF
room_no INT NOT NULL, message TEXT, type TEXT, date INT, INDEX system_message_index(room_no, date)
EOF;
    SendQuery("CREATE TABLE {$table}({$query})");
    echo $title . $str;
  }
  elseif(0 < $revision && $revision < 152){
    SendQuery("ALTER TABLE {$table} MODIFY room_no INT NOT NULL"); //room_no の型を変更
    echo $title . 'の room_no の型を "INT NOT NULL" に変更しました' . $footer;

    if($revision < 140){ //INDEX を設定
      SendQuery("ALTER TABLE {$table} ADD INDEX system_message_index(room_no, date)");
      echo $title . 'に INDEX (room_no, date) を設定しました' . $footer;
    }
  }

  $table = 'user_icon';
  $title = $header . ' (' . $table . ') ';
  if(! in_array($table, $table_list)){
    $query = <<<EOF
icon_no INT PRIMARY KEY, icon_name TEXT, icon_filename TEXT, icon_width INT, icon_height INT,
color TEXT, session_id TEXT, appearance TEXT, category TEXT, author TEXT, regist_date DATETIME,
disable BOOL
EOF;
    SendQuery("CREATE TABLE {$table}({$query})");
    echo $title . $str;

    //身代わり君のアイコンを登録 (No. 0)
    $class = new DummyBoyIcon(); //身代わり君アイコンの設定をロード
    SendQuery("INSERT INTO ${table}(icon_no, icon_name, icon_filename, icon_width,
		icon_height,color)
		VALUES(0, '{$class->name}', '{$class->path}', {$class->width},
		{$class->height}, '{$class->color}')");

    //初期アイコン登録
    $class = new DefaultIcon(); //ユーザアイコンの初期設定をロード
    $query = <<<EOF
INSERT INTO {$table}(icon_no, icon_name, icon_filename, icon_width, icon_height, color)
VALUES
EOF;
    foreach($class->data as $id => $list){
      extract($list);
      SendQuery("{$query}($id, '$name', '$file', $width, $height, '$color')");
      echo "ユーザアイコン ($id $file $name $width × $height $color) を登録しました" . $footer;
    }
  }
  elseif($revision > 0){ //追加フィールド処理
    $column = FetchArray('SHOW COLUMNS FROM ' . $table);
    $stack  = array(
      'appearance'  => 'TEXT',
      'category'    => 'TEXT',
      'author'      => 'TEXT',
      'regist_date' => 'DATETIME',
      'disable'     => 'BOOL',);
    foreach($stack as $name => $type){
      if(in_array($name, $column)) continue;
      $status = SendQuery("ALTER TABLE {$table} ADD {$name} {$type}") ? $success : $failed;
      echo $title . 'にフィールド (' . $name . $status . $footer;
    }
  }

  $title = $header . '(admin_manage)';
  if(! in_array('admin_manage', $table_list)){
    SendQuery("CREATE TABLE admin_manage(session_id TEXT)");
    SendQuery("INSERT INTO admin_manage VALUES('')");
    echo $title . $str;
  }

  mysql_query("GRANT ALL ON {$DB_CONF->name}.* TO {$DB_CONF->user}");
  SendCommit();
  echo '初期設定は無事完了しました' . $footer;
}
