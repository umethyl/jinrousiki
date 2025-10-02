<?php
require_once(dirname(__FILE__) . '/../include/functions.php');

if(! $DEBUG_MODE){
  OutputHTMLHeader('認証エラー', 'action', '../css');
  echo '</head><body>'."\n";
  echo 'このスクリプトは使用できない設定になっています。'."\n";
  OutputHTMLFooter(true);
}

extract($_GET, EXTR_PREFIX_ALL, 'unsafe');
$room_no = (int)$unsafe_room_no;
if($room_no < 1){
  OutputHTMLHeader('部屋削除[エラー]', 'action', '../css');
  echo '</head><body>'."\n";
  echo '無効な村番号です。'."\n";
  OutputHTMLFooter(true);
}

$connection = ConnectDatabase(); //DB 接続
mysql_query(sprintf("DELETE FROM talk WHERE room_no=%d", $room_no));
mysql_query(sprintf("DELETE FROM system_message WHERE room_no=%d", $room_no));
mysql_query(sprintf("DELETE FROM vote WHERE room_no=%d", $room_no));
mysql_query(sprintf("DELETE FROM user_entry WHERE room_no=%d", $room_no));
mysql_query(sprintf("DELETE FROM room WHERE room_no=%d", $room_no));
DisconnectDatabase($connection); //DB 接続解除

OutputHTMLHeader('部屋削除', 'action', '../css');
echo <<< EOF
<meta http-equiv="Refresh" content="1;URL='../index.php'">
</head><body>
$room_no 番地を削除しました。トップページに戻ります。<br>
切り替わらないなら <a href="../index.php">ここ</a> 。
</body></html>

EOF
?>
