<?php
//mbstring���б��ξ�硢���ߥ�졼������Ѥ���
if(! extension_loaded('mbstring')){
  require_once(dirname(__FILE__) . '/../module/mb-emulator.php');
}
require_once(dirname(__FILE__) .  '/setting.php');

//�ǡ����١�����³
//$header : ���Ǥ� HTML�إå������Ϥ���� [���� / ���ʤ�]
//$exit   : ���顼���� [HTML ���Ĥ��� exit ���֤� / false �ǽ�λ]
function ConnectDatabase($header = false, $exit = true){
  global $db_host, $db_uname, $db_pass, $db_name;

  if(! ($db_handle = mysql_connect($db_host, $db_uname, $db_pass))){
    if($header){
      echo "<font color=\"#FF0000\">MySQL��³����: $db_host</font><br>";
      if($exit)
	OutputHTMLFooter($exit);
      else
	return false;
    }
    else{
      OutputActionResult('MySQL��³����', "MySQL��³����: $db_host");
    }
  }

  mysql_set_charset('ujis');
  if(! mysql_select_db($db_name, $db_handle)){
    if($header){
      echo "<font color=\"#FF0000\">�ǡ����١�����³����: $db_name</font><br>";
      if($exit)
	OutputHTMLFooter($exit);
      else
	return false;
    }
    else{
      OutputActionResult('�ǡ����١�����³����', "�ǡ����١�����³����: $db_name");
    }
  }

  return $db_handle;
}

//�ǡ����١����Ȥ���³���Ĥ���
function DisconnectDatabase($dbHandle){
  mysql_close($dbHandle);
}

//ȯ���� DB ����Ͽ���� (talk Table)
function InsertTalk($room_no, $date, $location, $uname, $time, $sentence, $font_type, $spend_time){
  mysql_query("INSERT INTO talk(room_no, date, location, uname, time,
				sentence, font_type, spend_time)
		VALUES($room_no, $date, '$location', '$uname', '$time',
				'$sentence', '$font_type', $spend_time)");
}

//���å����ID�򿷤�������(PHP�ΥС�����󤬸Ť��Ȥ��δؿ���̵���Τ��������)
if(! function_exists('session_regenerate_id')){
  function session_regenerate_id(){
    $QQ = serialize($_SESSION);
    session_destroy();
    session_id(md5(uniqid(rand(), 1)));
    session_start();
    $_SESSION = unserialize($QQ);
  }
}

/**
 * �¹ԴĶ��˥��᡼����Ϳ�����ǽ���������ͤ��ޤޤ�Ƥ��뤫�ɤ����������ޤ���
 * @param  : mixed   : $value �����оݤ��ѿ�
 * @param  : boolean : $found ���路���ͤ�¸�ߤ��Ƥ��뤫�ɤ����򼨤��͡�
                       �����ͤ�true�ξ�硢����Ū�˾ܺ٤ʥ�����󤬼¹Ԥ���ޤ���
 * @return : boolean : �����ͤ�ȯ�����줿���true������ʳ��ξ��false
 */
function FindDangerValue($value, $found = false){
  if($found || (strpos(str_replace('.', '', serialize($value)), '22250738585072011') !== false)){
    //ʸ������������ο����������ޤ�Ƥ��륱�������ӽ�����
    if(is_array($value)){
      foreach($value as $item){
        if(FindDangerValue($item, true)) return true;
      }
    }
    else{
      $item = strval($value);
      $matches = '';
      if(preg_match('/^([0.]*2[0125738.]{15,16}1[0.]*)e(-[0-9]+)$/i', $item, $matches)){
        $exp = intval($matches[2]) + 1;
        if(2.2250738585072011e-307 === floatval("{$matches[1]}e{$exp}")) return true;
      }
    }
  }
  return false;
}

//TZ �����򤫤���������֤� (�Ķ��ѿ� TZ ���ѹ��Ǥ��ʤ��Ķ����ꡩ)
function TZTime(){
  global $OFFSET_SECONDS;
  return time() + $OFFSET_SECONDS;
}

//����(��)���Ѵ�����
function ConvertTime($seconds){
  $sentence = '';
  $hours    = 0;
  $minutes  = 0;

  if($seconds >= 60){
    $minutes = floor($seconds / 60);
    $seconds %= 60;
  }
  if($minutes >= 60){
    $hours = floor($minutes / 60);
    $minutes %= 60;
  }

  if($hours   > 0) $sentence .= $hours   . '����';
  if($minutes > 0) $sentence .= $minutes . 'ʬ';
  if($seconds > 0) $sentence .= $seconds . '��';
  return $sentence;
}

//POST���줿�ǡ�����ʸ�������ɤ����줹��
function EncodePostData(){
  global $ENCODE;

  foreach($_POST as $key => $value){
    $encode_type = mb_detect_encoding($value, 'ASCII, JIS, UTF-8, EUC-JP, SJIS');
    if($encode_type != '' && $encode_type != $ENCODE)
      $_POST[$key] = mb_convert_encoding($value, $ENCODE, $encode_type);
  }
}

//���϶ػ�ʸ���Υ����å�
function CheckForbiddenStrings($str){
  return (strstr($str, "'") || strstr($str, "\\"));
}

//�ü�ʸ���Υ��������׽���
//htmlentities() ��Ȥ���ʸ�������򵯤����Ƥ��ޤ��褦�ʤΤǴ����Ƥ٤��˽���
function EscapeStrings(&$str, $trim = true){
  if(get_magic_quotes_gpc()) $str = stripslashes($str); // \ ��ư�ǤĤ���������к�
  // $str = htmlentities($str, ENT_QUOTES); //UTF �˰ܹԤ����鵡ǽ���롩
  $str = str_replace('&' , '&amp;' , $str);
  $str = str_replace('<' , '&lt;'  , $str);
  $str = str_replace('>' , '&gt;'  , $str);
  $str = str_replace('\\', '&yen;' , $str);
  $str = str_replace('"' , '&quot;', $str);
  $str = str_replace("'" , '&#039;', $str);
  if($trim)
    $str = trim($str); //����ζ���Ȳ��ԥ����ɤ���
  else
    $str = str_replace(array("\r\n", "\r", "\n"), "\n", $str); //���ԥ����ɤ�����
}

//���ԥ����ɤ� <br> ���Ѵ����� (nl2br() ���� <br /> �ʤΤ� HTML 4.01 �����Ը���)
function LineToBR(&$str){
  $str = str_replace("\n", '<br>', $str);
}

//���� HTML �إå�����
//$path �� $CSS_PATH �ߤ����ʥ����Х��ѿ�����Ǥ���ȳڤ��ʡ�
function OutputHTMLHeader($title, $css = 'action', $path = 'css'){
  global $ENCODE;

  echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Strict//EN">
<html lang="ja"><head>
<meta http-equiv="Content-Type" content="text/html; charset={$ENCODE}">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>{$title}</title>
<link rel="stylesheet" href="{$path}/{$css}.css">

EOF;
}

//��̥ڡ��� HTML �إå�����
function OutputActionResultHeader($title, $url = ''){
  global $day_night;

  OutputHTMLHeader($title);
  if($url != '') echo '<meta http-equiv="Refresh" content="1;URL=' . $url . '">'."\n";
  if($day_night != '')  echo '<link rel="stylesheet" href="css/game_' . $day_night . '.css">'."\n";
  echo '</head><body>'."\n";
}

//��̥ڡ�������
function OutputActionResult($title, $body, $url = '', $unlock = false){
  global $dbHandle;

  if($unlock) mysql_query('UNLOCK TABLES'); //��å����
  if($dbHandle != '') DisconnectDatabase($dbHandle); //DB ��³���

  OutputActionResultHeader($title, $url);
  echo $body . "\n";
  OutputHTMLFooter(true);
}

//HTML �եå�����
function OutputHTMLFooter($exit = false){
  echo '</body></html>'."\n";
  if($exit) exit;
}
?>
