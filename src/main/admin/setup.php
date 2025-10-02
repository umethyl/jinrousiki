<?php
require_once(dirname(__FILE__) . '/../include/functions.php');

OutputHTMLHeader('汝は人狼なりや？[初期設定]', 'action', '../css'); //HTMLヘッダ

if(! ($dbHandle = ConnectDatabase(true, false))){ //DB 接続
  mysql_query("CREATE DATABASE $db_name DEFAULT CHARSET ujis");
  echo "データベース $db_name を作成しました。<br>";
  $dbHandle = ConnectDatabase(true); //改めて DB 接続
}
echo '</head><body>'."\n";

CheckTable(); //テーブル作成
OutputHTMLFooter(); //HTMLフッタ
DisconnectDatabase($dbHandle); //DB 接続解除

//-- クラス定義 --//
//ユーザアイコンの初期設定
//アイコンイメージをPHP設置時に追加する場合はここも必ず追加してください。
class DefaultIcon{
  //ユーザアイコンディレクトリ：setup.php からの相対パス
  //実際に運用する際は TOP からの相対パス (IconConfig->path) を参照する点に注意
  var $path   = '../user_icon';  //アイコン名のリスト

  var $name = array('明灰', '暗灰', '黄色', 'オレンジ', '赤', '水色', '青', '緑', '紫', 'さくら色');

  //アイコンの色 (アイコンのファイル名は必ず001〜の数字にしてください), 幅, 高さ
  var $color = array('#DDDDDD', '#999999', '#FFD700', '#FF9900', '#FF0000',
		     '#99CCFF', '#0066FF', '#00EE00', '#CC00CC', '#FF9999');
  var $width  = array(32, 32, 32, 32, 32, 32, 32, 32, 32, 32);
  var $height = array(32, 32, 32, 32, 32, 32, 32, 32, 32, 32);
}

//身代わり君アイコン
class DummyBoyIcon{
  var $path   = '../img/dummy_boy_user_icon.jpg'; //IconConfig->path からの相対パス
  var $name   = '身代わり君用'; //名前
  var $color  = '#000000'; //色
  var $width  = 45; //幅
  var $height = 45; //高さ
}

//-- 関数 --//
//必要なテーブルがあるか確認する
function CheckTable(){
  global $ICON_CONF, $db_name, $system_password;

  //テーブルのリストを配列に取得
  $sql   = mysql_list_tables($db_name);
  $count = mysql_num_rows($sql);
  $table = array();
  for($i=0; $i < $count; $i++) array_push($table, mysql_tablename($sql, $i));

  //チェックしてテーブルが存在しなければ作成する
  if(! in_array('room', $table)){
    mysql_query("CREATE TABLE room(room_no int primary key, room_name text, room_comment text,
		max_user int, game_option text, option_role text, status text,
		date int, day_night text,last_updated text,victory_role text)");
    echo 'テーブル(room)を作成しました<br>'."\n";
  }
  if(! in_array('user_entry', $table)){
    mysql_query("CREATE TABLE user_entry(room_no int, user_no int, uname text, handle_name text,
		icon_no int, profile text, sex text, password text, role text, live text,
		session_id char(32) unique, last_words text, ip_address text, last_load_day_night text)");
    echo 'テーブル(user_entry)を作成しました<br>'."\n";

    mysql_query("INSERT INTO user_entry(room_no, user_no, uname, handle_name, icon_no, profile,
		password, role, live)
		VALUES(0, 0, 'system', 'システム', 1, 'ゲームマスター', '$system_password', 'GM', 'live')");
  }
  if(! in_array('talk', $table)){
    mysql_query("CREATE TABLE talk(room_no int, date int, location text, uname text, time text,
		 sentence text, font_type text, spend_time int)");
    echo 'テーブル(talk)を作成しました<br>'."\n";
  }
  if(! in_array('vote', $table)){
    mysql_query("CREATE TABLE vote(room_no int NOT NULL, date int, uname text, target_uname text,
		 vote_number int, vote_times int, situation text)");
    echo 'テーブル(vote)を作成しました<br>'."\n";
  }
  if(! in_array('system_message', $table)){
    mysql_query("CREATE TABLE system_message(room_no int, message text, type text, date int)");
    echo 'テーブル(system_message)を作成しました<br>'."\n";
  }
  if(! in_array('user_icon', $table)){
    mysql_query("CREATE TABLE user_icon(icon_no int primary key, icon_name text, icon_filename text,
		icon_width int, icon_height int, color text, session_id text)");
    echo 'テーブル(user_icon)を作成しました<br>'."\n";

    //身代わり君のアイコンを登録(アイコンNo：0)
    $class = new DummyBoyIcon(); //身代わり君アイコンの設定をロード
    mysql_query("INSERT INTO user_icon(icon_no, icon_name, icon_filename, icon_width,
		 icon_height,color)
		 VALUES(0, '{$class->name}', '{$class->path}', {$class->width},
		 {$class->height}, '{$class->color}')");

    //初期のアイコンのファイル名と色データを DB に登録する
    $icon_no = 1;
    $class = new DefaultIcon(); //ユーザアイコンの初期設定をロード

    //ディレクトリ内のファイル一覧を取得
    if($handle = opendir($class->path)){
      while (($file = readdir($handle)) !== false){
	if($file != '.' && $file != '..' && $file != 'index.html'){
	  //初期データの読み込み
	  $name   = $class->name[  $icon_no - 1];
	  $width  = $class->width[ $icon_no - 1];
	  $height = $class->height[$icon_no - 1];
	  $color  = $class->color[ $icon_no - 1];

	  mysql_query("INSERT INTO user_icon(icon_no, icon_name, icon_filename, icon_width,
			icon_height, color)
			VALUES($icon_no, '$name', '$file', $width, $height, '$color')");
	  $icon_no++;
	  echo "ユーザアイコン($file $name $width × $height $color)を登録しました<br>"."\n";
	}
      }
      closedir($handle);
    }
  }

  if(! in_array('admin_manage', $table)){
    mysql_query("CREATE TABLE admin_manage(session_id text)");
    mysql_query("INSERT INTO admin_manage VALUES('')");
    echo 'テーブル(admin_manage)を作成しました<br>'."\n";
  }
  mysql_query("GRANT ALL ON {$db_name}.* TO $db_uname");
  mysql_query('COMMIT'); //一応コミット
  echo '初期設定は無事完了しました。<br>'."\n";
}
?>
