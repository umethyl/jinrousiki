<?php
require_once(dirname(__FILE__) . '/include/functions.php');

//��ե�������å�
$icon_upload_check_page_url = $site_root . 'icon_upload_check.php';
if(strncmp(@$_SERVER['HTTP_REFERER'], $icon_upload_check_page_url,
	   strlen($icon_upload_check_page_url)) != 0){
  OutputActionResult('����������Ͽ��λ�ڡ���[���顼]', '̵���ʥ��������Ǥ���');
}

$icon_no = (int)$_POST['icon_no'];
switch($_POST['entry']){
case 'success': //���å����ID�����DB������
  $dbHandle = ConnectDatabase(); //DB ��³

  //���å����ID�򥯥ꥢ
  mysql_query("UPDATE user_icon SET session_id = NULL WHERE icon_no = $icon_no");
  mysql_query('COMMIT');

  OutputActionResult('����������Ͽ��λ',
		     '��Ͽ��λ��������������Υڡ��������Ӥޤ���<br>'."\n" .
		     '�ڤ��ؤ��ʤ��ʤ� <a href="icon_view.php">����</a> ��',
		     'icon_view.php');
  break;

case 'cancel': //DB���饢������Υե�����̾����Ͽ���Υ��å����ID�����
  $dbHandle = ConnectDatabase(); //DB ��³

  $sql = mysql_query("SELECT icon_filename, session_id FROM user_icon WHERE icon_no = $icon_no");
  $array = mysql_fetch_assoc($sql);
  $file       = $array['icon_filename'];
  $session_id = $array['session_id'];

  //���å���󥹥�����
  session_start();
  if($session_id != session_id()){
    OutputActionResult('��������������',
		       '������ԡ����åץ��ɥ��å���󤬰��פ��ޤ���<br>'."\n" .
		       '<a href="index.php">�ȥåץڡ��������</a>');
  }
  unlink($ICON_CONF ->path . '/' . $file);
  mysql_query("DELETE FROM user_icon WHERE icon_no = $icon_no");
  mysql_query('COMMIT'); //������ߥå�

  //DB ��³����� OutputActionResult() ��ͳ
  OutputActionResult('������������λ',
		     '�����λ����Ͽ�ڡ��������Ӥޤ���<br>'."\n" .
		     '�ڤ��ؤ��ʤ��ʤ� <a href="icon_upload.php">����</a> ��',
		     'icon_upload.php');
  break;

default:
  OutputActionResult('����������Ͽ��λ�ڡ���[���顼]', '̵���ʥ��������Ǥ���');
  break;
}
?>
