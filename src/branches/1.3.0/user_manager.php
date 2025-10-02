<?php
require_once(dirname(__FILE__) . '/include/functions.php');

EncodePostData();//�ݥ��Ȥ��줿ʸ����򥨥󥳡��ɤ���

if($_GET['room_no'] == ''){
  OutputActionResult('¼����Ͽ [¼�ֹ楨�顼]',
		     '���顼��¼���ֹ椬����ǤϤ���ޤ���<br>'."\n" .
		     '<a href="index.php">�����</a>');
}

$dbHandle = ConnectDatabase(); //DB ��³

if($_POST['command'] == 'entry'){
  // if($GAME_CONF->trip) require_once(dirname(__FILE__) . '/include/convert_trip.php');
  EntryUser((int)$_GET['room_no'], $_POST['uname'], $_POST['handle_name'], (int)$_POST['icon_no'],
	     $_POST['profile'], $_POST['password'], $_POST['sex'], $_POST['role']);
}
else{
  OutputEntryUserPage((int)$_GET['room_no']);
}

DisconnectDatabase($dbHandle); //DB ��³���

// �ؿ� //
//�桼������Ͽ����
function EntryUser($room_no, $uname, $handle_name, $icon_no, $profile, $password, $sex, $role){
  global $GAME_CONF, $MESSAGE;

  //�ȥ�åס����������׽���
  ConvertTrip($uname);
  ConvertTrip($handle_name);
  EscapeStrings($profile, false);
  EscapeStrings($password);

  //����ϳ������å�
  if($uname == '' || $handle_name == '' || $icon_no == '' || $profile == '' ||
     $password == '' || $sex == '' || $role == ''){
    OutputActionResult('¼����Ͽ [���ϥ��顼]',
		       '����ϳ�줬����ޤ���<br>'."\n" .
		       '�������Ϥ��Ƥ���������');
  }

  //�����ƥ�桼�������å�
  if($uname == 'dummy_boy' || $uname == 'system' ||
     $handle_name == '�����귯' || $handle_name == '�����ƥ�'){
    OutputActionResult('¼����Ͽ [���ϥ��顼]',
		       '������̾������Ͽ�Ǥ��ޤ���<br>'."\n" .
		       '�桼��̾��dummy_boy or system<br>'."\n" .
		       '¼�ͤ�̾���������귯 or �����ƥ�');
  }

  //�����������å�
  $query = "SELECT COUNT(uname) FROM user_entry WHERE room_no = $room_no";

  //�桼��̾��¼��̾
  $sql = mysql_query("$query AND (uname = '$uname' OR handle_name = '$handle_name') AND user_no > 0");
  if(mysql_result($sql, 0, 0) != 0){
    OutputActionResult('¼����Ͽ [��ʣ��Ͽ���顼]',
		       '�桼��̾���ޤ���¼��̾��������Ͽ���Ƥ���ޤ���<br>'."\n" .
		       '�̤�̾���ˤ��Ƥ���������');
  }

  //���å����줿�ͤ�Ʊ���桼��̾
  $sql = mysql_query("$query AND uname = '$uname' AND user_no = -1");
  if(mysql_result($sql, 0, 0) != 0){
    OutputActionResult('¼����Ͽ [���å����줿�桼��]',
		       '���å����줿�ͤ�Ʊ���桼��̾�ϻ��ѤǤ��ޤ��� (¼��̾�ϲ�)<br>'."\n" .
		       '�̤�̾���ˤ��Ƥ���������');
  }

  //IP���ɥ쥹�����å�
  if($GAME_CONF->entry_one_ip_address){
    $ip_address = $_SERVER['REMOTE_ADDR']; //�桼����IP���ɥ쥹�����
    $sql = mysql_query("$query AND ip_address = '$ip_address' AND user_no > 0");
    if(mysql_result($sql, 0, 0) != 0){
      OutputActionResult('¼����Ͽ [¿����Ͽ���顼]', '¿����Ͽ�ϤǤ��ޤ���');
    }
  }

  //�ơ��֥���å�
  if(! mysql_query('LOCK TABLES room WRITE, user_entry WRITE, talk WRITE, admin_manage READ')){
    OutputActionResult('¼����Ͽ [�����Х��顼]',
		       '�����Ф��������Ƥ��ޤ���<br>'."\n" .
		       '������Ͽ���Ƥ�������');
  }

  //���å����κ��
  $system_time = TZTime(); //���߻�������
  $cookie_time = $system_time - 3600;
  setcookie('day_night',  '', $cookie_time);
  setcookie('vote_times', '', $cookie_time);
  setcookie('objection',  '', $cookie_time);

  //DB����桼��No��߽�˼���
  $sql = mysql_query("SELECT user_no FROM user_entry WHERE room_no = $room_no
			AND user_no > 0 ORDER BY user_no DESC");
  $array = mysql_fetch_assoc($sql);
  $user_no = (int)$array['user_no'] + 1; //�Ǥ��礭�� No + 1

  //DB�������Ϳ������
  $sql = mysql_query("SELECT day_night, status, max_user FROM room WHERE room_no = $room_no");
  $array  = mysql_fetch_assoc($sql);
  $day_night = $array['day_night'];
  $status    = $array['status'];
  $max_user  = $array['max_user'];

  //��������С����Ƥ���Ȥ�
  if($user_no > $max_user || $day_night != 'beforegame' || $status != 'waiting'){
    OutputActionResult('¼����Ͽ [��¼�Բ�]',
		       '¼�������������������ब���Ϥ���Ƥ��ޤ���', '', true);
  }

  //���å���󳫻�
  session_start();
  $session_id = '';

  do{ //DB ����Ͽ����Ƥ��륻�å���� ID �����ʤ��褦�ˤ���
    session_regenerate_id();
    $session_id = session_id();
    $sql = mysql_query("SELECT COUNT(room_no) FROM user_entry, admin_manage
			WHERE user_entry.session_id = '$session_id'
			OR admin_manage.session_id = '$session_id'");
  }while(mysql_result($sql, 0, 0) != 0);

  //DB �˥桼���ǡ�����Ͽ
  $entry = mysql_query("INSERT INTO user_entry(room_no, user_no, uname, handle_name,
			icon_no, profile, sex, password, role, live, session_id,
			last_words, ip_address, last_load_day_night)
			VALUES($room_no, $user_no, '$uname', '$handle_name', $icon_no,
			'$profile', '$sex', '$password', '$role', 'live',
			'$session_id', '', '$ip_address', 'beforegame')");

  //��¼��å�����
  InsertTalk($room_no, 0, 'beforegame system', 'system', $system_time,
	     $handle_name . ' ' . $MESSAGE->entry_user, NULL, 0);

  mysql_query('COMMIT'); //������ߥå�
  //��Ͽ���������Ƥ��ơ�����Υ桼�����Ǹ�Υ桼���ʤ��罸��λ����
  // if($entry && ($user_no == $max_user))
  //   mysql_query("update room set status = 'playing' where room_no = $room_no");

  if($entry){
    $url = "game_frame.php?room_no=$room_no";
    OutputActionResult('¼����Ͽ',
		       $user_no . ' ���ܤ�¼����Ͽ��λ��¼�δ��礤�ڡ��������Ӥޤ���<br>'."\n" .
		       '�ڤ��ؤ��ʤ��ʤ� <a href="' . $url. '">����</a> ��',
		       $url, true);
  }
  else{
    OutputActionResult('¼����Ͽ [�ǡ����١��������Х��顼]',
		       '�ǡ����١��������Ф��������Ƥ��ޤ���<br>'."\n" .
		       '���֤��֤��ƺ�����Ͽ���Ƥ���������', '', true);
  }
  mysql_query('UNLOCK TABLES'); //��å����
}

//�ȥ�å��Ѵ�����
function ConvertTrip(&$str){
  global $GAME_CONF;

  if($GAME_CONF->trip){ //�ޤ���������Ƥ��ޤ���
    OutputActionResult('¼����Ͽ [���ϥ��顼]',
                       '�ȥ�å��Ѵ������ϼ�������Ƥ��ޤ���<br>'."\n" .
                       '�����Ԥ��䤤��碌�Ƥ���������');
    // if(strrpos($str, '��') !== false){
    //   OutputActionResult('¼����Ͽ [���ϥ��顼]',
    // 			 '���� "��" ���Ѥ����ȥ�åפˤ�̤�б��Ǥ���<br>'."\n" .
    // 			 'Ⱦ�� "#" ����Ѥ��Ʋ�������');
    // }
    // $str = filterKey2Trip($str, 'cp51932'); //ʸ�������ɤ� convert_trip.php ����
  }
  else{
    if(strrpos($str, '#') !== false || strrpos($str, '��') !== false){
      OutputActionResult('¼����Ͽ [���ϥ��顼]',
			 '�ȥ�åפϻ����ԲĤǤ���<br>'."\n" .
			 '"#" ��ʸ��������ԲĤǤ���');
    }
  }
  EscapeStrings($str);
}

//�桼����Ͽ����ɽ��
function OutputEntryUserPage($room_no){
  global $ICON_CONF;
  $sql = mysql_query("select room_name, room_comment, status, game_option, option_role
			from room where room_no = $room_no");
  if(mysql_num_rows($sql) == 0){
    OutputActionResult('¼����Ͽ [¼�ֹ楨�顼]', "No.$room_no ���Ϥ�¼��¸�ߤ��ޤ���");
  }

  $array = mysql_fetch_assoc($sql);
  $room_name    = $array['room_name'];
  $room_comment = $array['room_comment'];
  $status       = $array['status'];
  $game_option  = $array['game_option'];
  $option_role  = $array['option_role'];
  if($status != 'waiting'){
    OutputActionResult('¼����Ͽ [��¼�Բ�]', '¼�������������������ब���Ϥ���Ƥ��ޤ���');
  }

  //�桼�������������
  $sql_icon = mysql_query("select icon_no, icon_name, icon_filename, icon_width, icon_height, color
				from user_icon where icon_no > 0 order by icon_no");
  $count  = mysql_num_rows($sql_icon); //�����ƥ�θĿ������
  $trip_str = '(�ȥ�å׻���' . ($GAME_CONF->trip ? '��ǽ' : '�Բ�') . ')';

  OutputHTMLHeader('��Ͽ�ϵ�ʤ�䡩[¼����Ͽ]', 'entry_user');
  echo <<<HEADER
</head>
<body>
<a href="index.php">�����</a><br>
<form method="POST" action="user_manager.php?room_no=$room_no">
<input type="hidden" name="command" value="entry">
<div align="center">
<table class="main">
<tr><td><img src="img/user_regist_title.gif"></td></tr>
<tr><td class="title">$room_name ¼<img src="img/user_regist_top.gif"></td></tr>
<tr><td class="number">��{$room_comment}�� [{$room_no} ����]</td></tr>
<tr><td>
<table class="input">
<tr>
<td class="img"><img src="img/user_regist_uname.gif"></td>
<td><input type="text" name="uname" size="30" maxlength="30"></td>
<td class="explain">���ʤ�ɽ�����줺��¾�Υ桼��̾���狼��Τ�<br>��˴�����Ȥ��ȥ����ཪλ��ΤߤǤ�{$trip_str}</td>
</tr>
<tr>
<td class="img"><img src="img/user_regist_handle_name.gif"></td>
<td><input type="text" name="handle_name" size="30" maxlength="30"></td>
<td class="explain">¼��ɽ�������̾���Ǥ�</td>
</tr>
<tr>
<td class="img"><img src="img/user_regist_password.gif"></td>
<td><input type="password" name="password" size="30" maxlength="30"></td>
<td class="explain">���å�����ڤ줿���˥�������˻Ȥ��ޤ�<br> (�Ź沽����Ƥ��ʤ��Τ������)</td>
</tr>
<tr>
<td class="img"><img src="img/user_regist_sex.gif"></td>
<td class="img">
<label for="male"><img src="img/user_regist_sex_male.gif"><input type="radio" id="male" name="sex" value="male"></label>
<label for="female"><img src="img/user_regist_sex_female.gif"><input type="radio" id="female" name="sex" value="female"></label>
</td>
<td class="explain">�ä˰�̣��̵������ġ�</td>
</tr>
<tr>
<td class="img"><img src="img/user_regist_profile.gif"></td>
<td colspan="2">
<textarea name="profile" cols="30" rows="2"></textarea>
<input type="hidden" name="role" value="none">
</td>
</tr>

HEADER;

  if(strpos($game_option, 'wish_role') !== false){
    echo <<<IMAGE
<tr>
<td class="role"><img src="img/user_regist_role.gif"></td>
<td>
<label for="none"><img src="img/user_regist_role_none.gif"><input type="radio" id="none" name="role" value="none"></label>
<label for="human"><img src="img/user_regist_role_human.gif"><input type="radio" id="human" name="role" value="human"></label><br>
<label for="wolf"><img src="img/user_regist_role_wolf.gif"><input type="radio" id="wolf" name="role" value="wolf"></label>
<label for="mage"><img src="img/user_regist_role_mage.gif"><input type="radio" id="mange" name="role" value="mage"></label><br>
<label for="necromancer"><img src="img/user_regist_role_necromancer.gif"><input type="radio" id="necromancer" name="role" value="necromancer"></label>
<label for="mad"><img src="img/user_regist_role_mad.gif"><input type="radio" id="mand" name="role" value="mad"></label><br>
<label for="guard"><img src="img/user_regist_role_guard.gif"><input type="radio" id="guard" name="role" value="guard"></label>
<label for="common"><img src="img/user_regist_role_common.gif"><input type="radio" id="common" name="role" value="common"></label><br>
<label for="fox"><img src="img/user_regist_role_fox.gif"><input type="radio" id="fox" name="role" value="fox"></label>

IMAGE;
    if(strpos($option_role, 'poison') !== false){
      echo '<label for="poison"><img src="img/user_regist_role_poison.gif">' .
	'<input type="radio" id="poison" name="role" value="poison"></label><br>';
    }
    elseif(strpos($option_role, 'cupid') !== false){
      ;
    }
    else{
      echo '<br>';
    }
    if(strpos($option_role, 'cupid') !== false){
      echo '<label for="cupid"><img src="img/user_regist_role_cupid.gif">' .
	'<input type="radio" id="cupid" name="role" value="cupid"></label><br>';
    }
    echo '</td><td></td>';
  }
  else{
    echo '<input type="hidden" name="role" value="none">';
  }

  echo <<<BODY
  </tr>
  <tr>
    <td class="submit" colspan="3"><input type="submit" value="¼����Ͽ����"></td>
  </tr>
</table>
</td></tr>

<tr><td>
<fieldset><legend><img src="img/user_regist_icon.gif"></legend>
<table class="icon">
<tr>

BODY;

  //ɽ�ν���
  for($i=0; $i < $count; $i++){
    if($i > 0 && ($i % 5) == 0) echo '</tr><tr>'; //5�Ĥ��Ȥ˲���
    $array = mysql_fetch_assoc($sql_icon);
    $icon_no       = $array['icon_no'];
    $icon_name     = $array['icon_name'];
    $icon_filename = $array['icon_filename'];
    $icon_width    = $array['icon_width'];
    $icon_height   = $array['icon_height'];
    $color         = $array['color'];
    $icon_location = $ICON_CONF->path . '/' . $icon_filename;

    echo <<<ICON
<td><label for="$icon_no"><img src="$icon_location" width="$icon_width" height="$icon_height" style="border-color:$color;">
$icon_name<br><font color="$color">��</font><input type="radio" id="$icon_no" name="icon_no" value="$icon_no"></label></td>

ICON;
  }

  echo <<<FOOTER
</tr></table>
</fieldset>
</td></tr>

</table></div></form>
</body></html>

FOOTER;
}
?>
