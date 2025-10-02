<?php
require_once(dirname(__FILE__) . '/include/functions.php');

EncodePostData();//ポストされた文字列をエンコードする

if($_GET['room_no'] == ''){
  OutputActionResult('村人登録 [村番号エラー]',
		     'エラー：村の番号が正常ではありません。<br>'."\n" .
		     '<a href="index.php">←戻る</a>');
}

$dbHandle = ConnectDatabase(); //DB 接続

if($_POST['command'] == 'entry'){
  // if($GAME_CONF->trip) require_once(dirname(__FILE__) . '/include/convert_trip.php');
  EntryUser((int)$_GET['room_no'], $_POST['uname'], $_POST['handle_name'], (int)$_POST['icon_no'],
	     $_POST['profile'], $_POST['password'], $_POST['sex'], $_POST['role']);
}
else{
  OutputEntryUserPage((int)$_GET['room_no']);
}

DisconnectDatabase($dbHandle); //DB 接続解除

// 関数 //
//ユーザを登録する
function EntryUser($room_no, $uname, $handle_name, $icon_no, $profile, $password, $sex, $role){
  global $GAME_CONF, $MESSAGE;

  //トリップ＆エスケープ処理
  ConvertTrip($uname);
  ConvertTrip($handle_name);
  EscapeStrings($profile, false);
  EscapeStrings($password);

  //記入漏れチェック
  if($uname == '' || $handle_name == '' || $icon_no == '' || $profile == '' ||
     $password == '' || $sex == '' || $role == ''){
    OutputActionResult('村人登録 [入力エラー]',
		       '記入漏れがあります。<br>'."\n" .
		       '全部入力してください。');
  }

  //システムユーザチェック
  if($uname == 'dummy_boy' || $uname == 'system' ||
     $handle_name == '身代わり君' || $handle_name == 'システム'){
    OutputActionResult('村人登録 [入力エラー]',
		       '下記の名前は登録できません。<br>'."\n" .
		       'ユーザ名：dummy_boy or system<br>'."\n" .
		       '村人の名前：身代わり君 or システム');
  }

  //項目被りチェック
  $query = "SELECT COUNT(uname) FROM user_entry WHERE room_no = $room_no";

  //ユーザ名、村人名
  $sql = mysql_query("$query AND (uname = '$uname' OR handle_name = '$handle_name') AND user_no > 0");
  if(mysql_result($sql, 0, 0) != 0){
    OutputActionResult('村人登録 [重複登録エラー]',
		       'ユーザ名、または村人名が既に登録してあります。<br>'."\n" .
		       '別の名前にしてください。');
  }

  //キックされた人と同じユーザ名
  $sql = mysql_query("$query AND uname = '$uname' AND user_no = -1");
  if(mysql_result($sql, 0, 0) != 0){
    OutputActionResult('村人登録 [キックされたユーザ]',
		       'キックされた人と同じユーザ名は使用できません。 (村人名は可)<br>'."\n" .
		       '別の名前にしてください。');
  }

  //IPアドレスチェック
  if($GAME_CONF->entry_one_ip_address){
    $ip_address = $_SERVER['REMOTE_ADDR']; //ユーザのIPアドレスを取得
    $sql = mysql_query("$query AND ip_address = '$ip_address' AND user_no > 0");
    if(mysql_result($sql, 0, 0) != 0){
      OutputActionResult('村人登録 [多重登録エラー]', '多重登録はできません。');
    }
  }

  //テーブルをロック
  if(! mysql_query('LOCK TABLES room WRITE, user_entry WRITE, talk WRITE, admin_manage READ')){
    OutputActionResult('村人登録 [サーバエラー]',
		       'サーバが混雑しています。<br>'."\n" .
		       '再度登録してください');
  }

  //クッキーの削除
  $system_time = TZTime(); //現在時刻を取得
  $cookie_time = $system_time - 3600;
  setcookie('day_night',  '', $cookie_time);
  setcookie('vote_times', '', $cookie_time);
  setcookie('objection',  '', $cookie_time);

  //DBからユーザNoを降順に取得
  $sql = mysql_query("SELECT user_no FROM user_entry WHERE room_no = $room_no
			AND user_no > 0 ORDER BY user_no DESC");
  $array = mysql_fetch_assoc($sql);
  $user_no = (int)$array['user_no'] + 1; //最も大きい No + 1

  //DBから最大人数を取得
  $sql = mysql_query("SELECT day_night, status, max_user FROM room WHERE room_no = $room_no");
  $array  = mysql_fetch_assoc($sql);
  $day_night = $array['day_night'];
  $status    = $array['status'];
  $max_user  = $array['max_user'];

  //定員オーバーしているとき
  if($user_no > $max_user || $day_night != 'beforegame' || $status != 'waiting'){
    OutputActionResult('村人登録 [入村不可]',
		       '村が既に満員か、ゲームが開始されています。', '', true);
  }

  //セッション開始
  session_start();
  $session_id = '';

  do{ //DB に登録されているセッション ID と被らないようにする
    session_regenerate_id();
    $session_id = session_id();
    $sql = mysql_query("SELECT COUNT(room_no) FROM user_entry, admin_manage
			WHERE user_entry.session_id = '$session_id'
			OR admin_manage.session_id = '$session_id'");
  }while(mysql_result($sql, 0, 0) != 0);

  //DB にユーザデータ登録
  $entry = mysql_query("INSERT INTO user_entry(room_no, user_no, uname, handle_name,
			icon_no, profile, sex, password, role, live, session_id,
			last_words, ip_address, last_load_day_night)
			VALUES($room_no, $user_no, '$uname', '$handle_name', $icon_no,
			'$profile', '$sex', '$password', '$role', 'live',
			'$session_id', '', '$ip_address', 'beforegame')");

  //入村メッセージ
  InsertTalk($room_no, 0, 'beforegame system', 'system', $system_time,
	     $handle_name . ' ' . $MESSAGE->entry_user, NULL, 0);

  mysql_query('COMMIT'); //一応コミット
  //登録が成功していて、今回のユーザが最後のユーザなら募集を終了する
  // if($entry && ($user_no == $max_user))
  //   mysql_query("update room set status = 'playing' where room_no = $room_no");

  if($entry){
    $url = "game_frame.php?room_no=$room_no";
    OutputActionResult('村人登録',
		       $user_no . ' 番目の村人登録完了、村の寄り合いページに飛びます。<br>'."\n" .
		       '切り替わらないなら <a href="' . $url. '">ここ</a> 。',
		       $url, true);
  }
  else{
    OutputActionResult('村人登録 [データベースサーバエラー]',
		       'データベースサーバが混雑しています。<br>'."\n" .
		       '時間を置いて再度登録してください。', '', true);
  }
  mysql_query('UNLOCK TABLES'); //ロック解除
}

//トリップ変換処理
function ConvertTrip(&$str){
  global $GAME_CONF;

  if($GAME_CONF->trip){ //まだ実装されていません
    OutputActionResult('村人登録 [入力エラー]',
                       'トリップ変換処理は実装されていません。<br>'."\n" .
                       '管理者に問い合わせてください。');
    // if(strrpos($str, '＃') !== false){
    //   OutputActionResult('村人登録 [入力エラー]',
    // 			 '全角 "＃" を用いたトリップには未対応です。<br>'."\n" .
    // 			 '半角 "#" を使用して下さい。');
    // }
    // $str = filterKey2Trip($str, 'cp51932'); //文字コードは convert_trip.php 参照
  }
  else{
    if(strrpos($str, '#') !== false || strrpos($str, '＃') !== false){
      OutputActionResult('村人登録 [入力エラー]',
			 'トリップは使用不可です。<br>'."\n" .
			 '"#" の文字も使用不可です。');
    }
  }
  EscapeStrings($str);
}

//ユーザ登録画面表示
function OutputEntryUserPage($room_no){
  global $ICON_CONF;
  $sql = mysql_query("select room_name, room_comment, status, game_option, option_role
			from room where room_no = $room_no");
  if(mysql_num_rows($sql) == 0){
    OutputActionResult('村人登録 [村番号エラー]', "No.$room_no 番地の村は存在しません。");
  }

  $array = mysql_fetch_assoc($sql);
  $room_name    = $array['room_name'];
  $room_comment = $array['room_comment'];
  $status       = $array['status'];
  $game_option  = $array['game_option'];
  $option_role  = $array['option_role'];
  if($status != 'waiting'){
    OutputActionResult('村人登録 [入村不可]', '村が既に満員か、ゲームが開始されています。');
  }

  //ユーザアイコン一覧
  $sql_icon = mysql_query("select icon_no, icon_name, icon_filename, icon_width, icon_height, color
				from user_icon where icon_no > 0 order by icon_no");
  $count  = mysql_num_rows($sql_icon); //アイテムの個数を取得
  $trip_str = '(トリップ使用' . ($GAME_CONF->trip ? '可能' : '不可') . ')';

  OutputHTMLHeader('汝は人狼なりや？[村人登録]', 'entry_user');
  echo <<<HEADER
</head>
<body>
<a href="index.php">←戻る</a><br>
<form method="POST" action="user_manager.php?room_no=$room_no">
<input type="hidden" name="command" value="entry">
<div align="center">
<table class="main">
<tr><td><img src="img/user_regist_title.gif"></td></tr>
<tr><td class="title">$room_name 村<img src="img/user_regist_top.gif"></td></tr>
<tr><td class="number">〜{$room_comment}〜 [{$room_no} 番地]</td></tr>
<tr><td>
<table class="input">
<tr>
<td class="img"><img src="img/user_regist_uname.gif"></td>
<td><input type="text" name="uname" size="30" maxlength="30"></td>
<td class="explain">普段は表示されず、他のユーザ名がわかるのは<br>死亡したときとゲーム終了後のみです{$trip_str}</td>
</tr>
<tr>
<td class="img"><img src="img/user_regist_handle_name.gif"></td>
<td><input type="text" name="handle_name" size="30" maxlength="30"></td>
<td class="explain">村で表示される名前です</td>
</tr>
<tr>
<td class="img"><img src="img/user_regist_password.gif"></td>
<td><input type="password" name="password" size="30" maxlength="30"></td>
<td class="explain">セッションが切れた場合にログイン時に使います<br> (暗号化されていないので要注意)</td>
</tr>
<tr>
<td class="img"><img src="img/user_regist_sex.gif"></td>
<td class="img">
<label for="male"><img src="img/user_regist_sex_male.gif"><input type="radio" id="male" name="sex" value="male"></label>
<label for="female"><img src="img/user_regist_sex_female.gif"><input type="radio" id="female" name="sex" value="female"></label>
</td>
<td class="explain">特に意味は無いかも……</td>
</tr>
<tr>
<td class="img"><img src="img/user_regist_profile.gif"></td>
<td colspan="2">
<textarea name="profile" cols="30" rows="2"></textarea>
<input type="hidden" name="role" value="none">
</td>
</tr>

HEADER;

  if(strpos($game_option, 'wish_role') !== false){
    echo <<<IMAGE
<tr>
<td class="role"><img src="img/user_regist_role.gif"></td>
<td>
<label for="none"><img src="img/user_regist_role_none.gif"><input type="radio" id="none" name="role" value="none"></label>
<label for="human"><img src="img/user_regist_role_human.gif"><input type="radio" id="human" name="role" value="human"></label><br>
<label for="wolf"><img src="img/user_regist_role_wolf.gif"><input type="radio" id="wolf" name="role" value="wolf"></label>
<label for="mage"><img src="img/user_regist_role_mage.gif"><input type="radio" id="mange" name="role" value="mage"></label><br>
<label for="necromancer"><img src="img/user_regist_role_necromancer.gif"><input type="radio" id="necromancer" name="role" value="necromancer"></label>
<label for="mad"><img src="img/user_regist_role_mad.gif"><input type="radio" id="mand" name="role" value="mad"></label><br>
<label for="guard"><img src="img/user_regist_role_guard.gif"><input type="radio" id="guard" name="role" value="guard"></label>
<label for="common"><img src="img/user_regist_role_common.gif"><input type="radio" id="common" name="role" value="common"></label><br>
<label for="fox"><img src="img/user_regist_role_fox.gif"><input type="radio" id="fox" name="role" value="fox"></label>

IMAGE;
    if(strpos($option_role, 'poison') !== false){
      echo '<label for="poison"><img src="img/user_regist_role_poison.gif">' .
	'<input type="radio" id="poison" name="role" value="poison"></label><br>';
    }
    elseif(strpos($option_role, 'cupid') !== false){
      ;
    }
    else{
      echo '<br>';
    }
    if(strpos($option_role, 'cupid') !== false){
      echo '<label for="cupid"><img src="img/user_regist_role_cupid.gif">' .
	'<input type="radio" id="cupid" name="role" value="cupid"></label><br>';
    }
    echo '</td><td></td>';
  }
  else{
    echo '<input type="hidden" name="role" value="none">';
  }

  echo <<<BODY
  </tr>
  <tr>
    <td class="submit" colspan="3"><input type="submit" value="村人登録申請"></td>
  </tr>
</table>
</td></tr>

<tr><td>
<fieldset><legend><img src="img/user_regist_icon.gif"></legend>
<table class="icon">
<tr>

BODY;

  //表の出力
  for($i=0; $i < $count; $i++){
    if($i > 0 && ($i % 5) == 0) echo '</tr><tr>'; //5個ごとに改行
    $array = mysql_fetch_assoc($sql_icon);
    $icon_no       = $array['icon_no'];
    $icon_name     = $array['icon_name'];
    $icon_filename = $array['icon_filename'];
    $icon_width    = $array['icon_width'];
    $icon_height   = $array['icon_height'];
    $color         = $array['color'];
    $icon_location = $ICON_CONF->path . '/' . $icon_filename;

    echo <<<ICON
<td><label for="$icon_no"><img src="$icon_location" width="$icon_width" height="$icon_height" style="border-color:$color;">
$icon_name<br><font color="$color">◆</font><input type="radio" id="$icon_no" name="icon_no" value="$icon_no"></label></td>

ICON;
  }

  echo <<<FOOTER
</tr></table>
</fieldset>
</td></tr>

</table></div></form>
</body></html>

FOOTER;
}
?>
