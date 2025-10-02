<?php
require_once(dirname(__FILE__) . '/include/game_functions.php');

$dbHandle = ConnectDatabase(); //DB ��³

//���å���󳫻�
session_start();
$session_id = session_id();

//�ѿ��򥻥å�
$room_no = (int)$_GET['room_no'];
$url     = 'game_frame.php?room_no=' . $room_no;
$header  = '��<br>' . "\n" . '�ڤ��ؤ��ʤ��ʤ� <a href="';
$footer  = '" target="_top">����</a> ��';
$anchor  = $header . $url . $footer;

//���������
//DB ��³����� OutputActionResult() ���Ԥ�
if($_POST['login_type'] == 'manually'){ //�桼��̾�ȥѥ���ɤǼ�ư������
  if(LoginManually($room_no))
    OutputActionResult('�����󤷤ޤ���', '�����󤷤ޤ���' . $anchor, $url);
  else
    OutputActionResult('��������', '�桼��̾�ȥѥ���ɤ����פ��ޤ���');
}
elseif(CheckSession($session_id, false)){ //���å����ID���鼫ư������
  OutputActionResult('�����󤷤Ƥ��ޤ�', '�����󤷤Ƥ��ޤ�' . $anchor, $url);
}
else{ //ñ�˸ƤФ줿�����ʤ����ڡ����˰�ư������
  $url    = 'game_view.php?room_no=' . $room_no;
  $anchor = $header . $url . $footer;
  OutputActionResult('����ڡ����˥�����', '����ڡ����˰�ư���ޤ�' . $anchor, $url);
}

// �ؿ� //
//�桼��̾�ȥѥ���ɤǥ�����
//�֤��͡�������Ǥ��� true / �Ǥ��ʤ��ä� false
function LoginManually($room_no){
  //���å����򼺤ä���硢�桼��̾�ȥѥ���ɤǥ����󤹤�
  $uname    = $_POST['uname'];
  $password = $_POST['password'];
  EscapeStrings($uname);
  EscapeStrings($password);

  if($uname == '' || $password == '') return false;

  // //IP���ɥ쥹����
  // $ip_address = $_SERVER['REMOTE_ADDR']; //�ä˻��Ȥ��Ƥʤ��褦�����ɡġ�

  //��������桼��̾�ȥѥ���ɤ����뤫��ǧ
  $sql = mysql_query("SELECT uname FROM user_entry WHERE room_no = $room_no
			AND uname = '$uname' AND password = '$password' AND user_no > 0");
  if(mysql_num_rows($sql) != 1) return false;

  // //�ä˻��Ȥ��Ƥʤ��褦�����ɡġ�
  // $array = mysql_fetch_assoc($sql);
  // $entry_uname = $array['uname'];

  //���å����ID�κ���Ͽ
  do{ //DB����Ͽ����Ƥ��륻�å����ID�����ʤ��褦�ˤ���
    session_start();
    session_regenerate_id();
    $session_id = session_id();

    $sql = mysql_query("SELECT COUNT(room_no) FROM user_entry, admin_manage
			WHERE user_entry.session_id = '$session_id'
			OR  admin_manage.session_id = '$session_id'");
  }while(mysql_result($sql, 0, 0) != 0);

  //DB�Υ��å����ID�򹹿�
  mysql_query("UPDATE user_entry SET session_id = '$session_id'
		WHERE room_no = $room_no AND uname = '$uname' AND user_no > 0");
  mysql_query('COMMIT'); //������ߥå�
  return true;
}
?>
