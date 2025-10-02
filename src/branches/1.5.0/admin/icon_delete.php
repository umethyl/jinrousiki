<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');

if(! $SERVER_CONF->debug_mode){
  OutputActionResult('認証エラー', 'このスクリプトは使用できない設定になっています。');
}

extract($_GET, EXTR_PREFIX_ALL, 'unsafe');
$icon_no = intval($unsafe_icon_no);
if($icon_no < 1) OutputActionResult('アイコン削除[エラー]', '無効なアイコン番号です。');

$INIT_CONF->LoadClass('ICON_CONF');
$DB_CONF->Connect(); //DB 接続
$file = FetchResult('SELECT icon_filename FROM user_icon WHERE icon_no = ' . $icon_no);
unlink($ICON_CONF->path . '/' . $file); //ファイルの存在をチェックしていないので要注意
SendQuery('DELETE FROM user_icon = ' . $icon_no);
OptimizeTable('user_icon');

//DB 接続解除は OutputActionResult() 経由
OutputActionResult('アイコン削除完了',
		   '削除完了：登録ページに飛びます。<br>'."\n" .
		   '切り替わらないなら <a href="../icon_upload.php">ここ</a> 。',
		   '../icon_upload.php');
