<?php
require_once(dirname(__FILE__) . '/include/functions.php');

//リファラチェック
$icon_upload_check_page_url = $site_root . 'icon_upload_check.php';
if(strncmp(@$_SERVER['HTTP_REFERER'], $icon_upload_check_page_url,
	   strlen($icon_upload_check_page_url)) != 0){
  OutputActionResult('アイコン登録完了ページ[エラー]', '無効なアクセスです。');
}

$icon_no = (int)$_POST['icon_no'];
switch($_POST['entry']){
case 'success': //セッションID情報をDBから削除
  $dbHandle = ConnectDatabase(); //DB 接続

  //セッションIDをクリア
  mysql_query("UPDATE user_icon SET session_id = NULL WHERE icon_no = $icon_no");
  mysql_query('COMMIT');

  OutputActionResult('アイコン登録完了',
		     '登録完了：アイコン一覧のページに飛びます。<br>'."\n" .
		     '切り替わらないなら <a href="icon_view.php">ここ</a> 。',
		     'icon_view.php');
  break;

case 'cancel': //DBからアイコンのファイル名と登録時のセッションIDを取得
  $dbHandle = ConnectDatabase(); //DB 接続

  $sql = mysql_query("SELECT icon_filename, session_id FROM user_icon WHERE icon_no = $icon_no");
  $array = mysql_fetch_assoc($sql);
  $file       = $array['icon_filename'];
  $session_id = $array['session_id'];

  //セッションスタート
  session_start();
  if($session_id != session_id()){
    OutputActionResult('アイコン削除失敗',
		       '削除失敗：アップロードセッションが一致しません。<br>'."\n" .
		       '<a href="index.php">トップページへ戻る</a>');
  }
  unlink($ICON_CONF ->path . '/' . $file);
  mysql_query("DELETE FROM user_icon WHERE icon_no = $icon_no");
  mysql_query('COMMIT'); //一応コミット

  //DB 接続解除は OutputActionResult() 経由
  OutputActionResult('アイコン削除完了',
		     '削除完了：登録ページに飛びます。<br>'."\n" .
		     '切り替わらないなら <a href="icon_upload.php">ここ</a> 。',
		     'icon_upload.php');
  break;

default:
  OutputActionResult('アイコン登録完了ページ[エラー]', '無効なアクセスです。');
  break;
}
?>
