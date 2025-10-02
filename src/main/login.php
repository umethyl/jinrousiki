<?php
require_once(dirname(__FILE__) . '/include/game_functions.php');

$dbHandle = ConnectDatabase(); //DB 接続

//セッション開始
session_start();
$session_id = session_id();

//変数をセット
$room_no = (int)$_GET['room_no'];
$url     = 'game_frame.php?room_no=' . $room_no;
$header  = '。<br>' . "\n" . '切り替わらないなら <a href="';
$footer  = '" target="_top">ここ</a> 。';
$anchor  = $header . $url . $footer;

//ログイン処理
//DB 接続解除は OutputActionResult() が行う
if($_POST['login_type'] == 'manually'){ //ユーザ名とパスワードで手動ログイン
  if(LoginManually($room_no))
    OutputActionResult('ログインしました', 'ログインしました' . $anchor, $url);
  else
    OutputActionResult('ログイン失敗', 'ユーザ名とパスワードが一致しません。');
}
elseif(CheckSession($session_id, false)){ //セッションIDから自動ログイン
  OutputActionResult('ログインしています', 'ログインしています' . $anchor, $url);
}
else{ //単に呼ばれただけなら観戦ページに移動させる
  $url    = 'game_view.php?room_no=' . $room_no;
  $anchor = $header . $url . $footer;
  OutputActionResult('観戦ページにジャンプ', '観戦ページに移動します' . $anchor, $url);
}

// 関数 //
//ユーザ名とパスワードでログイン
//返り値：ログインできた true / できなかった false
function LoginManually($room_no){
  //セッションを失った場合、ユーザ名とパスワードでログインする
  $uname    = $_POST['uname'];
  $password = $_POST['password'];
  EscapeStrings($uname);
  EscapeStrings($password);

  if($uname == '' || $password == '') return false;

  // //IPアドレス取得
  // $ip_address = $_SERVER['REMOTE_ADDR']; //特に参照してないようだけど…？

  //該当するユーザ名とパスワードがあるか確認
  $sql = mysql_query("SELECT uname FROM user_entry WHERE room_no = $room_no
			AND uname = '$uname' AND password = '$password' AND user_no > 0");
  if(mysql_num_rows($sql) != 1) return false;

  // //特に参照してないようだけど…？
  // $array = mysql_fetch_assoc($sql);
  // $entry_uname = $array['uname'];

  //セッションIDの再登録
  do{ //DBに登録されているセッションIDと被らないようにする
    session_start();
    session_regenerate_id();
    $session_id = session_id();

    $sql = mysql_query("SELECT COUNT(room_no) FROM user_entry, admin_manage
			WHERE user_entry.session_id = '$session_id'
			OR  admin_manage.session_id = '$session_id'");
  }while(mysql_result($sql, 0, 0) != 0);

  //DBのセッションIDを更新
  mysql_query("UPDATE user_entry SET session_id = '$session_id'
		WHERE room_no = $room_no AND uname = '$uname' AND user_no > 0");
  mysql_query('COMMIT'); //一応コミット
  return true;
}
?>
