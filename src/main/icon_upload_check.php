<?php
require_once(dirname(__FILE__) . '/include/functions.php');
require_once(dirname(__FILE__) . '/include/icon_data_check.php');
if(FindDangerValue($_FILES)) die;

//���å���󳫻�(�إå����������˳��Ϥ��Ƥ���)
session_start();
session_regenerate_id(); //���å����򿷤������
$session_id = session_id();

// ���顼�ڡ����ѥ����ȥ�
$title = '����������Ͽ���顼';

// ��ե�������å�
$icon_upload_page_url = $site_root . 'icon_upload.php';
if(strncmp(@$_SERVER['HTTP_REFERER'], $icon_upload_page_url , strlen($icon_upload_page_url)) != 0){
  OutputActionResult($title, '̵���ʥ��������Ǥ���');
}
EncodePostData(); //�ݥ��Ȥ��줿ʸ��������ƥ��󥳡��ɤ���

//��������̾�����򤫥����å�
$name = $_POST['name'];
if($name == '') OutputActionResult($title, '��������̾�����Ϥ��Ƥ���������');

//��������̾��ʸ����Ĺ�Υ����å�
$name_length = strlen($name);
if($name_length > $USER_ICON->name){
  OutputActionResult($title, IconNameMaxLength());
}

//�ե����륵�����Υ����å�
if($_FILES['file']['size'] == 0 || $_FILES['file']['size'] > $USER_ICON->size){
  OutputActionResult($title, '�ե����륵������ ' . IconFileSizeMax());
}

//�ե�����μ���Υ����å�
switch($_FILES['file']['type']){
case 'image/jpeg':
case 'image/pjpeg':
  $ext = '.jpg';
  break;

case 'image/gif':
  $ext = '.gif';
  break;

case 'image/png':
case 'image/x-png':
  $ext = '.png';
  break;

default:
  OutputActionResult($title, $_FILES['file']['type'] .
		     ' : jpg, gif, png �ʳ��Υե��������Ͽ�Ǥ��ޤ���');
  break;
}

//������Υ����å�
$color = $_POST['color'];
if(strlen($color) != 7 && ! preg_match('/^#[0123456789abcdefABCDEF]{6}/', $color)){
  OutputActionResult($title,
		     '�����꤬����������ޤ���<br>'."\n" .
		     '����� (�㡧#6699CC) �Τ褦�� RGB 16�ʿ�����ǹԤäƤ���������<br>'."\n" .
		     '�������줿������ �� <span class="color">' . $color . '</span>');
}

//��������ι⤵����������å�
list($width, $height) = getimagesize($_FILES['file']['tmp_name']);
if($width > $USER_ICON->width || $height > $USER_ICON->height){
  OutputActionResult($title, '��������� ' . IconSizeMax() . ' ������Ͽ�Ǥ��ޤ���<br>'."\n" .
		     '�������줿�ե����� �� <span class="color">�� ' . $width .
		     ', �⤵ ' . $height . '</span>');
}

$dbHandle = ConnectDatabase(); //DB ��³

//���������̾����������Ͽ����Ƥ��ʤ��������å�
$sql = mysql_query('SELECT icon_name FROM user_icon');
if(in_array($name, mysql_fetch_assoc($sql))){
  OutputActionResult($title, '���Υ�������̾�ϴ�����Ͽ����Ƥ��ޤ�');
}
EscapeStrings($name);

if(! mysql_query('LOCK TABLES user_icon WRITE')){ //user_icon �ơ��֥���å�
  OutputActionResult($title, '�����Ф��������Ƥ��ޤ���<br>'."\n" .
		     '���֤��֤��Ƥ������Ͽ�򤪴ꤤ���ޤ���');
}

//����������Ͽ���������ͤ�Ķ���Ƥʤ��������å�
//������Ͽ����Ƥ��륢������ʥ�С���߽�˼���
$sql = mysql_query('SELECT icon_no FROM user_icon ORDER BY icon_no DESC');
$array = mysql_fetch_assoc($sql);
$icon_no = $array['icon_no'] + 1; //�����礭��No + 1
if($icon_no >= $USER_ICON->number) OutputActionResult($title, '����ʾ���Ͽ�Ǥ��ޤ���', '', true);

//�ե�����̾�η�򤽤���
$file_name = sprintf("%03s%s", $icon_no, $ext);

//���åץ��ɤ��줿�ե�����Υ��顼�����å�
if($_FILES['upfile']['error'][$i] != 0){
  OutputActionResult($title, '�������åץ��ɥ��顼��ȯ�����ޤ�����<br>'."\n" .
		     '���ټ¹Ԥ��Ƥ���������', '', true);
}

//�ե������ƥ�ݥ�꤫�饳�ԡ�
if(! move_uploaded_file($_FILES['file']['tmp_name'], $ICON_CONF->path . '/' . $file_name)){
  OutputActionResult($title, '��Ͽ�˼��Ԥ��ޤ�����<br>'."\n" .
		     '���ټ¹Ԥ��Ƥ���������', '', true);
}

//�ǡ����١�������Ͽ
mysql_query("INSERT INTO user_icon(icon_no, icon_name, icon_filename, icon_width, icon_height,
		color, session_id)
		VALUES($icon_no, '$name', '$file_name', $width, $height, '$color', '$session_id')");
mysql_query('COMMIT'); //������ߥå�
mysql_query('UNLOCK TABLES'); //��å����
DisconnectDatabase($dbHandle);

//��ǧ�ڡ��������
OutputHTMLHeader('�桼���������󥢥åץ��ɽ���[��ǧ]', 'icon_upload_check');
echo <<<EOF
</head>
<body>
<p>�ե�����򥢥åץ��ɤ��ޤ�����<br>���������ʤ����Ǥ��ޤ�</p>
<img src="{$ICON_CONF->path}/$file_name" width="$width" height="$height"><br>
<table>
<tr><td>No. $icon_no <font color="$color">��</font>$color<br></td></tr>
<tr><td>������Ǥ�����</td></tr>
<tr><td><form method="POST" action="icon_upload_finish.php">
  <input type="hidden" name="entry" value="cancel">
  <input type="hidden" name="icon_no" value="$icon_no">
  <input type="submit" value="���ʤ���">
</form></td>
<td><form method="POST" action="icon_upload_finish.php">
  <input type="hidden" name="entry" value="success">
  <input type="hidden" name="icon_no" value="$icon_no">
  <input type="submit" value="��Ͽ��λ">
</form></td></tr></table>
</body></html>

EOF;
?>
