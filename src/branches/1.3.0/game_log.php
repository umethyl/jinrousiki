<?php
require_once(dirname(__FILE__) . '/include/game_functions.php');

//���å���󳫻�
session_start();
$session_id = session_id();

//���������
$room_no       = (int)$_GET['room_no'];
$log_mode      = $_GET['log_mode'];
$get_date      = (int)$_GET['date'];
$get_day_night = $_GET['day_night'];

$dbHandle = ConnectDatabase(); //DB ��³
$uname = CheckSession($session_id); //���å���� ID ������å�

//���դȥ���������
$sql = mysql_query("SELECT date, day_night, room_name, room_comment, game_option, status
			FROM room WHERE room_no = $room_no");
$array   = mysql_fetch_assoc($sql);
$date         = $array['date'];
$day_night    = $array['day_night'];
$room_name    = $array['room_name'];
$room_comment = $array['room_comment'];
$game_option  = $array['game_option'];
$status       = $array['status'];

//��ʬ�Υϥ�ɥ�͡��ࡢ��䡢��¸�����
$sql = mysql_query("SELECT user_no, handle_name, sex, role, live FROM user_entry
			WHERE room_no = $room_no AND uname = '$uname' AND user_no > 0");
$array  = mysql_fetch_assoc($sql);
$user_no     = $array['user_no'];
$handle_name = $array['handle_name'];
$sex         = $array['sex'];
$role        = $array['role'];
$live        = $array['live'];

if($live != 'dead' && $day_night != 'aftergame'){ //��Ԥ������ཪλ�����
  OutputActionResult('�桼��ǧ�ڥ��顼',
		     '���������ĥ��顼<br>' .
		     '<a href="index.php" target="_top">�ȥåץڡ���</a>' .
		     '��������󤷤ʤ����Ƥ�������');
}

$live = 'dead';
$date = $get_date;
$day_night = $get_day_night;

OutputGamePageHeader(); //HTML�إå�
echo '<table><tr><td width="1000" align="right">������ ' . $date . ' ���� (' .
  ($day_night == 'day' ? '��' : '��') . ')</td></tr></table>'."\n";
//OutputPlayerList();    //�ץ쥤�䡼�ꥹ��
OutputTalkLog();       //���å�
OutputAbilityAction(); //ǽ��ȯ��
OutputDeadMan();       //��˴��
if($day_night == 'night') OutputVoteList(); //��ɼ���
OutputHTMLFooter();
DisconnectDatabase($dbHandle); //DB ��³���
?>
