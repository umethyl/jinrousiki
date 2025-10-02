<?php
require_once(dirname(__FILE__) . '/../include/functions.php');
if(FindDangerValue($_FILES)) die;

EncodePostData();

//�ѿ��򥻥å�
$post = array('name'     => $_POST['name'],
	      'caption'  => $_POST['caption'],
	      'user'     => $_POST['user'],
	      'password' => $_POST['password']);
$label = array('name'     => '�ե�����̾',
	       'caption'  => '�ե����������',
	       'user'     => '������̾',
	       'password' => '�ѥ����');
$size = array('name'     => 20,
	      'caption'  => 80,
	      'user'     => 20,
	      'password' => 20);

//�����Υ��顼�����å�
foreach($post as $key => $value){
  //̤���ϥ����å�
  if($value == '') OutputUploadResult('<span>' . $label[$key] . '</span> ��̤���ϤǤ���');

  //ʸ����Ĺ�����å�
  if(strlen($value) > $size[$key]){
    OutputUploadResult('<span>' . $label[$key] . '</span> �� ' .
		       '<span>' . $size[$key] . '</span> ʸ���ʲ��ˤ��Ƥ���������');
  }

  //���������׽���
  EscapeStrings($value);
}

//�ѥ���ɤΥ����å�
if($post['password'] != $src_upload_password) OutputUploadResult('�ѥ����ǧ�ڥ��顼��');

//�ե�����μ���Υ����å�
$file_name = strtolower(trim($_FILES['file']['name']));
$file_type = $_FILES['file']['type'];
if(! (preg_match('/application\/(octet-stream|zip|lzh|lha|x-zip-compressed)/i', $file_type) &&
      preg_match('/^.*\.(zip|lzh)$/', $file_name))){
  OutputUploadResult('<span>' . $file_name . '</span> : <span>' . $file_type . '</span><br>'."\n".
		     'zip/lzh �ʳ��Υե�����ϥ��åץ��ɤǤ��ޤ���');
}

//�ե����륵�����Υ����å�
$file_size = $_FILES['file']['size'];
if($file_size == 0 || $file_size > 10 * 1024 * 1024){ //setting.php ������Ǥ���褦�ˤ���
  OutputUploadResult('�ե����륵������ <span>10 Mbyte</span> �ޤǡ�');
}


//�ե������ֹ�μ���
$number = (int)file_get_contents('file/number.txt');
if(! ($io = fopen('file/number.txt', 'wb+'))){ //�ե����륪���ץ�
  OutputUploadResult('�ե������ IO ���顼�Ǥ���<br>' .
		     '���֤򤪤��Ƥ��饢�åץ��ɤ��ʤ����Ƥ���������');
}
stream_set_write_buffer($io, 0); //�Хåե��� 0 �˻��� (��¾������ݾ�)

if(! flock($io, LOCK_EX)){ //�ե�����Υ�å�
  fclose($io);
  OutputUploadResult('�ե�����Υ�å����顼�Ǥ���<br>' .
		     '���֤򤪤��Ƥ��饢�åץ��ɤ��ʤ����Ƥ���������');
}
rewind($io); //�ե�����ݥ��󥿤���Ƭ�˰�ư
fwrite($io, $number + 1); //���󥯥���Ȥ��ƽ񤭹���

flock($io, LOCK_UN); //��å����
fclose($io); //�ե�����Υ�����

//HTML�����������
$number = sprintf("%04d", $number); //��·��
$ext    = substr($file_name, -3); //��ĥ��
$time   = gmdate('Y/m/d (D) H:i:s', TZTime()); //����
if($file_size > 1024 * 1024) // Mbyte
  $file_size = sprintf('%.2f', $file_size / (1024 * 1024)) . ' Mbyte';
elseif($file_size > 1024) // Kbyte
  $file_size = sprintf('%.2f', $file_size / 1024) . ' Kbyte';
else
  $file_size = sprintf('%.2f', $file_size) . ' byte';

$html = <<<EOF
<td class="link"><a href="file/{$number}.{$ext}">{$post['name']}</a></td>
<td class="type">$ext</td>
<td class="size">$file_size</td>
<td class="explain">{$post['caption']}</td>
<td class="name">{$post['user']}</td>
<td class="date">$time</td>

EOF;

if(! ($io = fopen('html/' . $number . '.html', 'wb+'))){ //�ե����륪���ץ�
  OutputUploadResult('�ե������ IO ���顼�Ǥ���<br>' .
		     '���֤򤪤��Ƥ��饢�åץ��ɤ��ʤ����Ƥ���������');
}
stream_set_write_buffer($io, 0); //�Хåե��� 0 �˻��� (��¾������ݾ�)

if(! flock($io, LOCK_EX)){ //�ե�����Υ�å�
  fclose($io);
  OutputUploadResult('�ե�����Υ�å����顼�Ǥ���<br>' .
		     '���֤򤪤��Ƥ��饢�åץ��ɤ��ʤ����Ƥ���������');
}
rewind($io); //�ե�����ݥ��󥿤���Ƭ�˰�ư
fwrite($io, $html); //�񤭹���

flock($io, LOCK_UN); //��å����
fclose($io); //�ե�����Υ�����

//�ե�����Υ��ԡ�
if(move_uploaded_file($_FILES['file']['tmp_name'], 'file/' . $number . '.' . $ext)){
  OutputUploadResult('�ե�����Υ��åץ��ɤ��������ޤ�����');
}
else{
  OutputUploadResult('�ե�����Υ��ԡ����ԡ�<br>' .
		     '���֤򤪤��Ƥ��饢�åץ��ɤ��ʤ����Ƥ���������');
}

// �ؿ� //
//��̽���
function OutputUploadResult($body){
  OutputHTMLHeader('�ե����륢�åץ��ɽ���', 'src', '../css');
  echo '</head><body>'."\n" . $body . '<br><br>'."\n" .
    '<a href="index.php">�����</a>'."\n";
  OutputHTMLFooter(true);
}
?>
