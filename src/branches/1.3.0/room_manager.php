<?php
require_once(dirname(__FILE__) . '/include/functions.php');

if(! $dbHandle = ConnectDatabase(true, false)) return false; //DB ��³

MaintenanceRoom();
EncodePostData();

if($_POST['command'] == 'CREATE_ROOM'){
  //��ե�������å�
  if(strncmp(@$_SERVER['HTTP_REFERER'], $site_root, strlen($site_root)) != 0)
    OutputActionResult('¼���� [���ϥ��顼]', '̵���ʥ��������Ǥ���');
  //¼�����ѥ���ɥ����å�
  elseif($ROOM_CONF->room_password != '' && $_POST['room_password'] != $ROOM_CONF->room_password)
    OutputActionResult('¼���� [���»���]', '¼�����ѥ���ɤ�����������ޤ���');
  //���ꤵ�줿�Ϳ������򤬤��뤫�����å�
  elseif (!in_array($_POST['max_user'], $ROOM_CONF->max_user_list))
     OutputActionResult('¼���� [���ϥ��顼]', '̵���ʺ���Ϳ��Ǥ���');
  else
    CreateRoom($_POST['room_name'], $_POST['room_comment'], $_POST['max_user']);
}
else{
  OutputRoomList();
}

DisconnectDatabase($dbHandle); //DB ��³���

//-- �ؿ� --//
//¼�Υ��ƥʥ󥹽���
function MaintenanceRoom(){
  global $ROOM_CONF;

  //������ֹ�����̵��¼����¼�ˤ���
  $list  = mysql_query("SELECT room_no, last_updated FROM room WHERE status <> 'finished'");
  $query = "UPDATE room SET status = 'finished', day_night = 'aftergame' WHERE room_no = ";
  MaintenanceRoomAction($list, $query, $ROOM_CONF->die_room);

  //��λ���������Υ��å����ID�Υǡ����򥯥ꥢ����
  $list = mysql_query("SELECT room.room_no, last_updated from room, user_entry
			WHERE room.room_no = user_entry.room_no
			AND !(user_entry.session_id is NULL) GROUP BY room_no");
  $query = "UPDATE user_entry SET session_id = NULL WHERE room_no = ";
  MaintenanceRoomAction($list, $query, $ROOM_CONF->clear_session_id);
}

//¼�Υ��ƥʥ󥹽��� (����)
function MaintenanceRoomAction($list, $query, $base_time){
  $count = mysql_num_rows($list);
  $time  = TZTime();

  for($i=0; $i < $count; $i++){
    $array = mysql_fetch_assoc($list);
    $room_no      = $array['room_no'];
    $last_updated = $array['last_updated'];
    $diff_time    = $time - $last_updated;
    if($diff_time > $base_time) mysql_query($query . $room_no);
  }
}

//¼(room)�κ���
function CreateRoom($room_name, $room_comment, $max_user){
  global $MESSAGE, $system_password;

  //���ϥǡ����Υ��顼�����å�
  if($room_name == '' || $room_comment == '' || ! ctype_digit($max_user)){
    OutputRoomAction('empty');
    return false;
  }

  if($_POST['game_option_real_time'] == 'real_time'){
    $day   = $_POST['game_option_real_time_day'];
    $night = $_POST['game_option_real_time_night'];

    //���»��֤�0����99����ο����������å�
    if($day   != '' && ! preg_match('/[^0-9]/', $day)   && $day   > 0 && $day   < 99 &&
       $night != '' && ! preg_match('/[^0-9]/', $night) && $night > 0 && $night < 99){
      $real_time_set_str = 'real_time:' . $day . ':' . $night;
    }
    else{
      OutputRoomAction('time');
      return false;
    }
  }

  $option_role = $_POST['option_role_decide'] . ' ' . $_POST['option_role_authority'] .
    ' ' . $_POST['option_role_poison'] . ' ' . $_POST['option_role_cupid'];

  $game_option = $_POST['game_option_wish_role'] . ' ' . $_POST['game_option_dummy_boy'] .
    ' ' . $_POST['game_option_open_vote'] . ' ' . $_POST['game_option_not_open_cast'] .
    ' ' . $real_time_set_str;

  if(! mysql_query('LOCK TABLES room WRITE, user_entry WRITE, vote WRITE, talk WRITE')){
    OutputRoomAction('busy');
    return false;
  }

  $result = mysql_query('SELECT room_no FROM room ORDER BY room_no DESC'); //�߽�˥롼��No�����
  $room_no_array = mysql_fetch_assoc($result); //�����(�Ǥ��礭��No)�����
  $room_no = $room_no_array['room_no'] + 1;

  //���������׽���
  EscapeStrings($room_name);
  EscapeStrings($room_comment);

  //��Ͽ
  $time = TZTime();
  $entry = mysql_query("INSERT INTO room(room_no, room_name, room_comment, game_option,
			option_role, max_user, status, date, day_night, last_updated)
			VALUES($room_no, '$room_name', '$room_comment', '$game_option',
			'$option_role', $max_user, 'waiting', 0, 'beforegame', '$time')");

  //�����귯����¼������
  if(strpos($game_option, 'dummy_boy') !== false){
    mysql_query("INSERT INTO user_entry(room_no, user_no, uname, handle_name, icon_no,
		   profile, sex, password, live, last_words, ip_address)
		   VALUES($room_no, 1, 'dummy_boy', '�����귯', 0, '{$MESSAGE->dummy_boy_comment}',
		   'male', '$system_password', 'live', '{$MESSAGE->dummy_boy_last_words}', '')");
  }

  if($entry && mysql_query('COMMIT')){ //������ߥå�
    OutputRoomAction('success', $room_name);
  }
  else{
    OutputRoomAction('busy');
  }
  mysql_query('UNLOCK TABLES');
}

//��̽��� (CreateRoom() ��)
function OutputRoomAction($type, $room_name = ''){
  switch($type){
    case 'empty':
      OutputActionResultHeader('¼���� [���ϥ��顼]');
      echo '���顼��ȯ�����ޤ�����<br>';
      echo '�ʲ��ι��ܤ���٤���ǧ����������<br>';
      echo '<ul><li>¼��̾������������Ƥ��ʤ���</li>';
      echo '<li>¼����������������Ƥ��ʤ���</li>';
      echo '<li>����Ϳ��������ǤϤʤ����ޤ��ϰ۾��ʸ����</li></ul>';
      break;

    case 'time':
      OutputActionResultHeader('¼���� [���ϥ��顼]');
      echo '���顼��ȯ�����ޤ�����<br>';
      echo '�ʲ��ι��ܤ���٤���ǧ����������<br>';
      echo '<ul><li>�ꥢ�륿���������롢��λ��֤������Ƥ��ʤ���</li>';
      echo '<li>�ꥢ�륿���������롢��λ��֤����Ѥ����Ϥ��Ƥ���</li>';
      echo '<li>�ꥢ�륿���������롢��λ��֤�0�ʲ����ޤ���99�ʾ�Ǥ���</li>';
      echo '<li>�ꥢ�륿���������롢��λ��֤������ǤϤʤ����ޤ��ϰ۾��ʸ����</li></ul>';
      break;

    case 'success':
      OutputActionResultHeader('¼����', 'index.php');
      echo "$room_name ¼��������ޤ������ȥåץڡ��������Ӥޤ���";
      echo '�ڤ��ؤ��ʤ��ʤ� <a href="index.php">����</a> ��';
      break;

    case 'busy':
      OutputActionResultHeader('¼���� [�ǡ����١������顼]');
      echo '�ǡ����١��������Ф��������Ƥ��ޤ���<br>'."\n";
      echo '���֤��֤��ƺ�����Ͽ���Ƥ���������';
      break;
  }
  OutputHTMLFooter(); //�եå�����
}

//¼(room)��waiting��playing�Υꥹ�Ȥ���Ϥ���
function OutputRoomList(){
  global $DEBUG_MODE, $ROOM_IMG;

  //�롼��No���롼��̾�������ȡ�����Ϳ������֤����
  $sql = mysql_query("SELECT room_no, room_name, room_comment, game_option, option_role, max_user,
			status FROM room WHERE status <> 'finished' ORDER BY room_no DESC ");
  if($sql == NULL) return false;

  while($array = mysql_fetch_assoc($sql)){
    $room_no      = $array['room_no'];
    $room_name    = $array['room_name'];
    $room_comment = $array['room_comment'];
    $game_option  = $array['game_option'];
    $option_role  = $array['option_role'];
    $max_user     = $array['max_user'];
    $status       = $array['status'];

    switch($status){
      case 'waiting':
	$status_img = $ROOM_IMG->waiting;
	break;

      case 'playing':
	$status_img = $ROOM_IMG->playing;
	break;
    }

    $option_img_str = ''; //�����४�ץ����β���
    if(strpos($game_option, 'wish_role') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->wish_role, '����˾��');
    if(strpos($game_option, 'real_time') !== false){
      //�»��֤����»��֤����
      $real_time_str = strstr($game_option, 'real_time');
      sscanf($real_time_str, "real_time:%d:%d", $day, $night);
      AddImgTag($option_img_str, $ROOM_IMG->real_time,
		"�ꥢ�륿���������롧 $day ʬ���롧 $night ʬ");
    }
    if(strpos($game_option, 'dummy_boy') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->dummy_boy, '��������Ͽ����귯');
    if(strpos($game_option, 'open_vote') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->open_vote, '��ɼ����ɼ�����ɽ����');
    if(strpos($game_option, 'not_open_cast') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->not_open_cast, '��������������ʤ�');
    if(strpos($option_role, 'decide') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->decide, '16�Ͱʾ�Ƿ�����о�');
    if(strpos($option_role, 'authority') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->authority, '16�Ͱʾ�Ǹ��ϼ��о�');
    if(strpos($option_role, 'poison') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->poison, '20�Ͱʾ�����Ǽ��о�');
    if(strpos($option_role, 'cupid') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->cupid, '���塼�ԥå��о�');

    $max_user_img = $ROOM_IMG -> max_user_list[$max_user]; //����Ϳ�

    echo <<<EOF
<a href="login.php?room_no=$room_no">
<img src="$status_img"><span>[{$room_no}����]</span>{$room_name}¼<br>
<div>���{$room_comment}��� {$option_img_str}<img src="$max_user_img"></div>
</a><br>

EOF;

    if($DEBUG_MODE){
      echo '<a href="admin/room_delete.php?room_no=' . $room_no . '">' . $room_no .
	' ���Ϥ��� (�۵���)</a><br>'."\n";
    }
  }
}

//���ץ������������ɲ� (OutputRoomList() ��)
function AddImgTag(&$tag, $src, $title){
  $tag .= "<img class=\"option\" src=\"$src\" title=\"$title\" alt=\"$title\">";
}
?>
