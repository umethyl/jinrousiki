<?php
require_once(dirname(__FILE__) . '/../include/functions.php');

if(! $DEBUG_MODE){
  OutputHTMLHeader('ǧ�ڥ��顼', 'action', '../css');
  echo '</head><body>'."\n";
  echo '���Υ�����ץȤϻ��ѤǤ��ʤ�����ˤʤäƤ��ޤ���'."\n";
  OutputHTMLFooter(true);
}

extract($_GET, EXTR_PREFIX_ALL, 'unsafe');
$room_no = (int)$unsafe_room_no;
if($room_no < 1){
  OutputHTMLHeader('�������[���顼]', 'action', '../css');
  echo '</head><body>'."\n";
  echo '̵����¼�ֹ�Ǥ���'."\n";
  OutputHTMLFooter(true);
}

$connection = ConnectDatabase(); //DB ��³
mysql_query(sprintf("DELETE FROM talk WHERE room_no=%d", $room_no));
mysql_query(sprintf("DELETE FROM system_message WHERE room_no=%d", $room_no));
mysql_query(sprintf("DELETE FROM vote WHERE room_no=%d", $room_no));
mysql_query(sprintf("DELETE FROM user_entry WHERE room_no=%d", $room_no));
mysql_query(sprintf("DELETE FROM room WHERE room_no=%d", $room_no));
DisconnectDatabase($connection); //DB ��³���

OutputHTMLHeader('�������', 'action', '../css');
echo <<< EOF
<meta http-equiv="Refresh" content="1;URL='../index.php'">
</head><body>
$room_no ���Ϥ������ޤ������ȥåץڡ��������ޤ���<br>
�ڤ��ؤ��ʤ��ʤ� <a href="../index.php">����</a> ��
</body></html>

EOF
?>
