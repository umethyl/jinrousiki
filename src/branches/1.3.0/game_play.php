<?php
require_once(dirname(__FILE__) . '/include/game_functions.php');

//���å���󳫻�
session_start();
$session_id = session_id();

EncodePostData(); //�ݥ��Ȥ��줿ʸ��������ƥ��󥳡��ɤ���

//���������
$room_no     = (int)$_GET['room_no']; //���� No
$auto_reload = (int)$_GET['auto_reload']; //�����ȥ���ɤδֳ�
$dead_mode   = $_GET['dead_mode'];   //��˴�ԥ⡼��
$heaven_mode = $_GET['heaven_mode']; //���å⡼��
// $view_mode   = $_GET['view_mode'];   //����⡼��
$list_down  = $_GET['list_down']; //�ץ쥤�䡼�ꥹ�Ȥ򲼤ˤ���
$play_sound = $_GET['play_sound'];//���Ǥ��Τ餻
if($play_sound == 'on'){
  $cookie_day_night  = $_COOKIE['day_night'];       //�������򲻤Ǥ��餻�뤿��
  $cookie_vote_times = (int)$_COOKIE['vote_times']; //����ɼ�򲻤��Τ餻�뤿��
  $cookie_objection  = $_COOKIE['objection'];       //�ְ۵Ĥ���פ򲻤��Τ餻�뤿��
}

$say = $_POST['say']; //ȯ��
$font_type = $_POST['font_type']; //�ե���ȥ�����
$set_objection = $_POST['set_objection']; //�ְ۵ġפ��ꡢ�Υ��å�

$dbHandle = ConnectDatabase(); //DB ��³
$uname = CheckSession($session_id); //���å���� ID ������å�

//���դȥ���������
$sql = mysql_query("SELECT date, day_night, room_name, room_comment, game_option FROM room
			WHERE room_no = $room_no");
$array = mysql_fetch_assoc($sql);
$room_name    = $array['room_name'];
$room_comment = $array['room_comment'];
$game_option  = $array['game_option'];
$date         = $array['date'];
$day_night    = $array['day_night'];

//��ʬ�Υϥ�ɥ�͡��ࡢ��䡢��¸�����
$sql = mysql_query("SELECT user_no, handle_name, sex, role, live, last_load_day_night FROM user_entry
			WHERE room_no = $room_no AND uname = '$uname' AND user_no > 0");
$array = mysql_fetch_assoc($sql);
$user_no             = $array['user_no'];
$handle_name         = $array['handle_name'];
$sex                 = $array['sex'];
$role                = $array['role'];
$live                = $array['live'];
$last_load_day_night = $array['last_load_day_night'];

$system_time = TZTime(); //���߻�������

//ɬ�פʥ��å����򥻥åȤ���
$objection_array = array(); //SendCookie();�ǳ�Ǽ����롦�۵Ĥ���ξ���
$objection_left_count = 0;  //SendCookie();�ǳ�Ǽ����롦�۵Ĥ���λĤ���
SendCookie();

//���ԤΥ����å�
$sql = mysql_query("SELECT victory_role FROM room WHERE room_no = $room_no");
$victory_flag = (mysql_result($sql, 0, 0) != NULL);

//ȯ����̵ͭ������å�
EscapeStrings($say, false); //���������׽���
if($say != '' && $font_type == 'last_words' && $live == 'live')
  EntryLastWords($say);  //�����Ƥ���а����Ͽ
elseif($say != '' && ($last_load_day_night == $day_night ||
		      $live == 'dead' || $uname == 'dummy_boy'))
  Say($say); //���Ǥ��뤫���Ǹ�˥���ɤ������ȥ����󤬰��פ��Ƥ��뤫�����귯�ʤ�񤭹���
else
  CheckSilence(); //���������ڤΥ����å�(���ۡ�������)

//�Ǹ�˥���ɤ������������ξ��֤򹹿�
mysql_query("UPDATE user_entry SET last_load_day_night = '$day_night'
		WHERE room_no = $room_no AND uname = '$uname' AND user_no > 0");
mysql_query('COMMIT');

OutputGamePageHeader(); //HTML�إå�
OutputGameHeader(); //�����Υ����ȥ�ʤ�

if($heaven_mode != 'on'){
  if($list_down != 'on') OutputPlayerList(); //�ץ쥤�䡼�ꥹ��
  OutputAbility(); //��ʬ����������
  if($day_night == 'day' && $live == 'live') CheckSelfVote(); //��ɼ�Ѥߥ����å�
  OutputReVoteList(); //����ɼ�λ�����å�������ɽ������
}

//���å������
if($live == 'dead' && $heaven_mode == 'on')
  OutputHeavenTalkLog();
else
  OutputTalkLog();

if($heaven_mode != 'on'){
  if($live == 'dead') OutputAbilityAction(); //ǽ��ȯ��
  OutputLastWords(); //���
  OutputDeadMan();   //��˴��
  OutputVoteList();  //��ɼ���
  if($dead_mode != 'on') OutputSelfLastWords(); //��ʬ�ΰ��
  if($list_down == 'on') OutputPlayerList(); //�ץ쥤�䡼�ꥹ��
}
OutputHTMLFooter();

DisconnectDatabase($dbHandle); //DB ��³���

//-- �ؿ� --//
//ɬ�פʥ��å�����ޤȤ����Ͽ(�Ĥ��Ǥ˺ǿ��ΰ۵Ĥ���ξ��֤������������˳�Ǽ)
function SendCookie(){
  global $GAME_CONF, $system_time, $room_no, $date, $day_night, $user_no, $live, $uname,
    $set_objection, $objection_array, $objection_left_count;

  //<�������򲻤Ǥ��Τ餻��>
  //���å����˳�Ǽ���������˲��Ǥ��Τ餻�ǻȤ���ͭ�����°���֡�
  setcookie('day_night', $day_night, $system_time + 3600);

  //<�ְ۵ġפ���򲻤Ǥ��Τ餻��>
  //���ޤǤ˼�ʬ���ְ۵ġפ���򤷤��������
  $sql = mysql_query("SELECT COUNT(message) FROM system_message WHERE room_no = $room_no
			AND type = 'OBJECTION' AND message = '$user_no'");

  //�����Ƥ���(�����ཪλ��ϻ�ԤǤ�OK)�ְ۵ġפ��ꡢ�Υ��å��׵᤬����Х��åȤ���(����������ξ��)
  if($live == 'live' && $day_night != 'night' && $set_objection == 'set' &&
     mysql_result($sql, 0, 0) < $GAME_CONF->objection){
    InsertSystemMessage($user_no, 'OBJECTION');
    InsertSystemTalk('OBJECTION', $system_time, '', '', $uname);
    mysql_query('COMMIT');
  }

  //�桼�������������ƿͿ�ʬ�Ρְ۵Ĥ���פΥ��å������ۤ���
  $sql = mysql_query("SELECT COUNT(uname) FROM user_entry WHERE room_no = $room_no AND user_no > 0");
  // //�����ꥻ�å� (0 ���ܤ��Ѥ��ͤ�����ʤ������ݾڤ���Ƥ�������פ��ʡ�)
  // $objection_array = array();
  // unset($objection_array[0]);
  $objection_array = array_fill(1, mysql_result($sql, 0, 0), 0); //index �� 1 ����

  //message:�۵Ĥ���򤷤��桼�� No �Ȥ��β�������
  $sql = mysql_query("SELECT message, COUNT(message) AS message_count FROM system_message
			WHERE room_no = $room_no AND type = 'OBJECTION' GROUP BY message");
  while(($array = mysql_fetch_assoc($sql)) !== false){
    $this_user_no = (int)$array['message'];
    $this_count   = (int)$array['message_count'];
    $objection_array[$this_user_no] = $this_count;
  }

  //���å����˳�Ǽ (ͭ�����°����)
  foreach($objection_array as $value){
    if($str != '') $str .= ','; //����޶��ڤ�
    $str .= $value;
  }
  setcookie('objection', $str, $system_time + 3600);

  //�Ĥ�۵Ĥ���β��
  $objection_left_count = $GAME_CONF->objection - $objection_array[$user_no];

  //<����ɼ�򲻤Ǥ��Τ餻��>
  //����ɼ�β�������
  $sql = mysql_query("SELECT message FROM system_message WHERE room_no = $room_no
			AND date = $date AND type = 'RE_VOTE' ORDER BY message DESC");
  if(mysql_num_rows($sql) != 0){
    $last_vote_times = (int)mysql_result($sql, 0, 0); //�����ܤκ���ɼ�ʤΤ�����
    setcookie('vote_times', $last_vote_times, $system_time + 3600); //���å����˳�Ǽ��ͭ�����°���֡�
  }
  else{
    setcookie('vote_times', '', $system_time - 3600); //���å�����������ͭ�����°���֡�
  }
}

//�����Ͽ
function EntryLastWords($say){
  global $room_no, $day_night, $uname, $live;

  //�����ब��λ���Ƥ��뤫�����Ǥ�������Ͽ���ʤ�
  if($day_night == 'aftergame' || $live != 'live') return false;

  //�����Ĥ�
  mysql_query("UPDATE user_entry SET last_words = '$say' WHERE room_no = $room_no
		AND uname = '$uname' AND user_no > 0");
  mysql_query('COMMIT'); //������ߥå�
}

//ȯ��
function Say($say){
  global $room_no, $game_option, $day_night, $uname, $role, $live;

  if(strpos($game_option, 'real_time') !== false){ //�ꥢ�륿������
    GetRealPassTime($left_time);
    $spend_time = 0; //���äǻ��ַв���������̵���ˤ���
  }
  else{ //���äǻ��ַв���
    GetTalkPassTime($left_time); //�в���֤���
    if(strlen($say) <= 100) //�в����
      $spend_time = 1;
    elseif(strlen($say) <= 200)
      $spend_time = 2;
    elseif(strlen($say) <= 300)
      $spend_time = 3;
    else
      $spend_time = 4;
  }

  if($day_night == 'beforegame' || $day_night == 'aftergame') //�����೫������Ϥ��Τޤ�ȯ��
    Write($say, $day_night, 0, true);
  elseif($uname == 'dummy_boy') //�����귯 (���� GM �б�)
    Write($say, $day_night, 0, false); //ȯ�����֤򹹿����ʤ�
  elseif($live == 'dead') //��˴�Ԥ�����
    Write($say, 'heaven', 0, false); //ȯ�����֤򹹿����ʤ�
  elseif($live == 'live' && $left_time > 0){ //��¸�Ԥ����»�����
    if($day_night == 'day') //��Ϥ��Τޤ�ȯ��
      Write($say, 'day', $spend_time, true);
    elseif($day_night == 'night'){ //��������ʬ����
      if(strpos($role, 'wolf') !== false) //ϵ
	Write($say, 'night wolf', $spend_time, true);
      elseif(strpos($role, 'common') !== false) //��ͭ��
	Write($say, 'night common', 0);
      elseif(strpos($role, 'fox') !== false) //�Ÿ�
	Write($say, 'night fox', 0);
      else //�Ȥ��
	Write($say, 'night self_talk', 0);
    }
  }
}

//ȯ���� DB ����Ͽ����
function Write($say, $location, $spend_time, $update = false){
  global $system_time, $room_no, $date, $uname, $font_type;

  InsertTalk($room_no, $date, $location, $uname, $system_time, $say, $font_type, $spend_time);
  if($update) UpdateTime();
  mysql_query('COMMIT'); //������ߥå�
}

//���������ڤΥ����å�
function CheckSilence(){
  global $TIME_CONF, $MESSAGE, $system_time, $room_no, $date, $game_option, $day_night;

  //��������ʳ��Ͻ����򤷤ʤ�
  if($day_night != 'day' && $day_night != 'night') return false;

  //�ơ��֥��å�
  if(! mysql_query("LOCK TABLES room WRITE, talk WRITE, vote WRITE,
			user_entry WRITE, system_message WRITE")){
    return false;
  }

  //�Ǹ��ȯ�����줿���֤����
  $sql = mysql_query("SELECT last_updated FROM room WHERE room_no = $room_no");
  $last_updated_time = mysql_result($sql, 0, 0);
  $last_update_diff_sec = $system_time - $last_updated_time;

  //�в���֤����
  if(strpos($game_option, 'real_time') !== false){ //�ꥢ�륿������
    GetRealPassTime($left_time);
  }
  else{ //���äǻ��ַв���
    $silence_pass_time = GetTalkPassTime($left_time, true);
  }

  //�ꥢ�륿�������Ǥʤ������»�������������ͤ�Ķ�����ʤ�ʤ����ֿʤ��(����)
  if(strpos($game_option, 'real_time') === false && $left_time > 0){
    if($last_update_diff_sec > $TIME_CONF->silence){
      $sentence = "�������������������� " . $silence_pass_time . ' ' . $MESSAGE->silence;
      InsertTalk($room_no, $date, "$day_night system", 'system', $system_time, $sentence, NULL, $TIME_CONF->silence_pass);
      UpdateTime();
    }
  }
  elseif($left_time == 0){
    //�ٹ��Ф�
    $left_time_str = ConvertTime($TIME_CONF->sudden_death); //������ޤǤ����»���
    $sudden_death_announce = "����" . $left_time_str . "��" . $MESSAGE->sudden_death_announce;

    //���˷ٹ��Ф��Ƥ��뤫�����å�
    $sql = mysql_query("SELECT COUNT(uname) FROM talk WHERE room_no = $room_no
			AND date = $date AND location = '$day_night system'
			AND uname = 'system' AND sentence = '$sudden_death_announce'");

    if(mysql_result($sql, 0, 0) == 0){ //�ٹ��Ф��Ƥ��ʤ��ä���Ф�
      InsertSystemTalk($sudden_death_announce, ++$system_time); //�����äθ�˽Ф�褦��
      UpdateTime(); //�������֤򹹿�
      $last_update_diff_sec = 0;
    }

    if($DEBUG_MODE){
      $sudden_death_exec_time = $TIME_CONF->sudden_death - $last_update_diff_sec;
      echo "����������ޤǤ��� $sudden_death_exec_time �� <br>"; //�ƥ���ɽ����
    }

    //���»��֤�᤮�Ƥ�����̤��ɼ�οͤ������व����
    if($last_update_diff_sec > $TIME_CONF->sudden_death){
      //��ɼ���Ƥ��ʤ��ͤ�������뤿��δ���SQLʸ
      //(��ɼ�Ѥߤοͤ򺸷�礷�ơ�����ɼ�Ѥ�=NULL����ɼ���Ƥ��ʤ��פ����)
      $query = "SELECT user_entry.uname, user_entry.handle_name, user_entry.role
		FROM user_entry left join tmp_sd on user_entry.uname = tmp_sd.uname
		WHERE user_entry.room_no = $room_no AND user_entry.live = 'live'
		AND user_entry.user_no > 0 AND tmp_sd.uname is NULL";
      if($day_night == 'day'){
	$sql = mysql_query("SELECT message FROM system_message WHERE room_no = $room_no
				AND date = $date AND type = 'VOTE_TIMES'");
	$vote_times = mysql_result($sql, 0, 0);

	//��ɼ�ѤߤοͤΥƥ�ݥ��ơ��֥�����
	mysql_query("CREATE TEMPORARY TABLE tmp_sd SELECT uname FROM vote
			WHERE room_no = $room_no AND date = $date
			AND situation = 'VOTE_KILL' AND vote_times = $vote_times");
	//��ɼ���Ƥ��ʤ��ͤ����
	$sql_novote = mysql_query($query);
      }
      elseif($day_night == 'night'){
	//��ɼ�ѤߤοͤΥƥ�ݥ��ơ��֥�����
	mysql_query("CREATE TEMPORARY TABLE tmp_sd SELECT uname FROM vote
			WHERE room_no = $room_no AND date = $date
			AND (situation = 'WOLF_EAT' or situation = 'MAGE_DO'
			or situation = 'GUARD_DO' or situation = 'CUPID_DO')");

	//��ϵ����ɼ��̵ͭ������å�����
	$wolf_query = "SELECT count(uname) FROM vote WHERE room_no = $room_no AND " .
	  "date = $date AND situation = 'WOLF_EAT'";
	$sql = mysql_query($wolf_query);
	$wolf_novote = mysql_result($sql, 0, 0) == 0;

	//��ɼ���Ƥ��ʤ��ͤ���� (�򿦤Τ�)
	$query .= " AND (" . ($wolf_novote ? "user_entry.role LIKE 'wolf%' or " : "") .
	  "user_entry.role LIKE 'mage%' or  user_entry.role LIKE '" .
	  ($date == 1 ? 'cupid' : 'guard') . "%')";
	$sql_novote = mysql_query($query);
      }

      //̤��ɼ�Ԥο�
      $novote_count = mysql_num_rows($sql_novote);

      //̤��ɼ�Ԥ����������व����
      for($i=0; $i < $novote_count; $i++){
	$array = mysql_fetch_assoc($sql_novote);
	$this_uname  = $array['uname'];
	$this_handle = $array['handle_name'];
	$this_role   = $array['role'];

	DeadUser($this_uname); //������¹�
	InsertSystemTalk($this_handle . $MESSAGE->sudden_death, ++$system_time); //�����ƥ��å�����

	//���ͤθ��ɤ�����
	if(strpos($this_role, 'lovers') !== false) LoversFollowed($this_role, true);
      }
      InsertSystemTalk($MESSAGE->vote_reset, ++$system_time); //��ɼ�ꥻ�åȥ�å�����
      InsertSystemTalk($sudden_death_announce, ++$system_time); //��������Υ�å�����
      UpdateTime(); //���»��֥ꥻ�å�

      //��ɼ�ꥻ�å�
      mysql_query("DELETE FROM vote WHERE room_no = $room_no AND situation <> 'CUPID_DO'");
      CheckVictory(); //���ԥ����å�
    }
  }
  mysql_query("UNLOCK TABLES"); //�ơ��֥��å����
}

//¼̾�������ϡ������ܡ����פޤǡ����֤����(���Ԥ��Ĥ�����¼��̾�������ϡ����Ԥ����)
function OutputGameHeader(){
  global $GAME_CONF, $MESSAGE, $SOUND, $system_time, $room_no, $room_name, $room_comment,
    $game_option, $dead_mode, $heaven_mode, $date, $day_night, $live, $handle_name,
    $auto_reload, $play_sound, $list_down, $cookie_day_night, $cookie_objection,
    $objection_array, $objection_left_count;

  $room_message = '<td class="room"><span>' . $room_name . '¼</span>����' . $room_comment .
    '��[' . $room_no . '����]</td>'."\n";
  $url_room      = '?room_no='     . $room_no;
  $url_day_night = '&day_night='   . $day_night;
  $url_reload    = ($auto_reload != '' ? '&auto_reload=' . $auto_reload : '');
  $url_sound     = ($play_sound  != '' ? '&play_sound='  . $play_sound  : '');
  $url_list      = ($list_down   != '' ? '&list_down='   . $list_down   : '');
  $url_dead      = ($dead_mode   != '' ? '&dead_mode='   . $dead_mode   : '');
  $url_heaven    = ($heaven_mode != '' ? '&heaven_mode=' . $heaven_mode : '');

  echo '<table class="game-header"><tr>'."\n";
  if(($live == 'dead' && $heaven_mode == 'on') || $day_night == 'aftergame'){ //��ȥ�������
    if($live == 'dead' && $heaven_mode == 'on')
      echo '<td>&lt;&lt;&lt;ͩ��δ�&gt;&gt;&gt;</td>'."\n";
    else
      echo $room_message;

    //�������Υ��ؤΥ������
    echo '<td class="view-option">��';

    $url_header ='<a href="game_log.php' . $url_room . '&log_mode=on&date=';
    $url_footer = '#game_top" target="_blank">';
    $url_day    = '&day_night=day'   . $url_footer;
    $url_night  = '&day_night=night' . $url_footer;

    echo $url_header . '1' . $url_night . '1(��)</a>'."\n";
    for($i=2; $i < $date; $i++){
      echo $url_header . $i . $url_day   . $i . '(��)</a>'."\n";
      echo $url_header . $i . $url_night . $i . '(��)</a>'."\n";
    }
    if($day_night == 'night' && $heaven_mode == 'on'){
      echo $url_header . $date . $url_day . $date . '(��)</a>'."\n";
    }
    elseif($day_night == 'aftergame'){
      $sql = mysql_query("SELECT COUNT(uname) FROM talk WHERE room_no = $room_no
				AND date = $date AND location = 'day'");
      if(mysql_num_rows($sql) > 0)
	echo $url_header . $date . $url_day . $date . '(��)</a>'."\n";
    }

    if($heaven_mode == 'on'){
      echo '</td>'."\n" . '</tr></table>'."\n";
      return;
    }
  }
  else{
    echo $room_message;
    echo '<td class="view-option">'."\n";

    if($live == 'dead' && $dead_mode == 'on'){ //��˴�Ԥξ��Ρ����������ɽ���Ͼ�⡼��
      $url = 'game_play.php' . $url_room . '&dead_mode=on' . $url_reload .
	$url_sound . $url_list . '#game_top';
      echo '<form method="POST" action="' . $url . '" name="reload_middle_frame" target="middle">'."\n";
      echo '<input type="submit" value="����">'."\n";
      echo '</form>'."\n";
    }
  }

  if($day_night != 'aftergame'){ //�����ཪλ��ϼ�ư�������ʤ�
    $url_header = '<a target="_top" href="game_frame.php' . $url_room .
      $url_dead . $url_heaven . $url_list;
    OutputAutoReloadLink($url_header . $url_sound  . '&auto_reload=');

    $url = $url_header . $url_reload . '&play_sound=';
    echo ' [���Ǥ��Τ餻](' .
      ($play_sound == 'on' ?  'on ' . $url . 'off">off</a>' : $url . 'on">on</a> off') .
      ')'."\n";
  }

  //�ץ쥤�䡼�ꥹ�Ȥ�ɽ������
  echo '<a target="_top" href="game_frame.php' . $url_room . $url_dead . $url_heaven .
    $url_reload . $url_sound  . '&list_down=' . ($list_down == 'on' ? 'off">��' : 'on">��') .
    '�ꥹ��</a>'."\n";

  //�������򲻤Ǥ��Τ餻����
  if($play_sound == 'on'){
    //�������ξ��
    if($cookie_day_night != $day_night && $day_night == 'day') OutputSound($SOUND->morning);

    //�۵Ĥ��ꡢ�򲻤��Τ餻��
    $cookie_objection_array = explode(',', $cookie_objection); //���å������ͤ�����˳�Ǽ����
/*
    $objection_sex = array();
    for($i=0; $i < 22; $i++){ //��ʬ��׻�
      if($objection_array[$i] > (int)$tmp[$i]){ //��ʬ������Ф������̤��ǧ����פ⥫�����
	$num = $i + 1;
	$sql = mysql_query("SELECT sex FROM user_entry WHERE room_no = $room_no AND user_no = $num");
	$array = mysql_fetch_assoc($sql); //�֤��ͤ򻲾Ȥ��Ƥ��ʤ��Τ� DB ����Ȥγ�ǧ��

	if(mysql_result($sql, 0, 0) == 'male') //�������Ĥ餻���ɤ��褦�ʡ�������
	  array_push($objection_sex, $SOUND->objection_male);
	else
	  array_push($objection_sex, $SOUND->objection_female);

	$objection_count++; //���
      }
    }

    for($i=0; $i < $objection_count; $i++){ //��ʬ������Ф��β�����������Ĥ餹
      OutputSound($objection_sex[$i], true);
    }
*/
    /*
    $count = count($objection_array);
    for($i = 1; $i <= $count; $i++){ //��ʬ��׻� (index �� 1 ����)
      //��ʬ����������̤��ǧ���Ʋ����Ĥ餹
      if((int)$objection_array[$i] > (int)$cookie_objection_array[$i]){
	$sql = mysql_query("SELECT sex FROM user_entry WHERE room_no = $room_no AND user_no = $i");
	$objection_sound = 'objection_' . (mysql_result($sql, 0, 0) == 'male') ? 'male' : 'female';
	OutputSound($SOUND->$objection_sound, true);
      }
    }
    */
  }
  echo '</td></tr>'."\n" . '</table>'."\n";

  switch($day_night){
    case 'beforegame': //����������դ����
      echo '<div class="caution">'."\n";
      echo '������򳫻Ϥ���ˤ������������೫�Ϥ���ɼ����ɬ�פ�����ޤ�';
      echo '<span>(��ɼ�����ͤ�¼�ͥꥹ�Ȥ��طʤ��֤��ʤ�ޤ�)</span>'."\n";
      echo '</div>'."\n";
      break;

    case 'day':
      $time_message = '�����פޤ� ';
      break;

    case 'night':
      $time_message = '���������ޤ� ';
      break;

    case 'aftergame': //���Է�̤���Ϥ��ƽ�����λ
      OutputVictory();
      return;
  }

  echo '<table class="time-table"><tr>'."\n";
  OutputTimeTable(); //�в���������¸�Ϳ������

  $left_time = 0;
  //�в���֤����
  if(strpos($game_option, 'real_time') !== false){ //�ꥢ�륿������
    GetRealPassTime($left_time);
  }
  else{ //���äǻ��ַв���
    $left_talk_time = GetTalkPassTime($left_time);
  }

  if($day_night == 'beforegame'){
    if(strpos($game_option, 'real_time') !== false){
      //�»��֤����»��֤����
      sscanf(strstr($game_option, 'time'), 'time:%d:%d', &$day_minutes, &$night_minutes);
      echo '<td class="real-time">';
      echo "������֡� �� <span>{$day_minutes}</span>ʬ / �� <span>{$night_minutes}</span>ʬ";

      //�������������ФȤλ��֥����ɽ��
      $date_str = gmdate('Y, m, j, G, i, s', $system_time);
      echo '<script type="text/javascript" src="javascript/output_diff_time.js"></script>'."\n";
      echo ' �����Фȥ�����PC�λ��֥���(�饰��)�� ' . '<span><script type="text/javascript">' .
	"output_diff_time('$date_str');" . '</script></span>' . '��</td>'."\n";
    }
  }
  elseif($day_night == 'day' || $day_night == 'night'){
    if(strpos($game_option, 'real_time') !== false){ //�ꥢ�륿������
      echo '<td class="real-time"><form name="realtime_form">'."\n";
      echo '<input type="text" name="output_realtime" size="50" readonly>'."\n";
      echo '</form></td>'."\n";
    }
    elseif($left_talk_time){ //ȯ���ˤ�벾�ۻ���
      echo '<td>' . $time_message . $left_talk_time . '</td>'."\n";
    }
  }

  //�۵Ĥ��ꡢ�Υܥ���(��Ȼ�ԥ⡼�ɰʳ�)
  if($day_night == 'beforegame' ||
     ($day_night == 'day' && $dead_mode != 'on' && $heaven_mode != 'on' && $left_time > 0)){
    $url = 'game_play.php' . $url_room . $url_reload . $url_sound . $url_list . '#game_top';
    echo <<<EOF
<td class="objection"><form method="POST" action="$url">
<input type="hidden" name="set_objection" value="set">
<input type="image" name="objimage" src="{$GAME_CONF->objection_image}" border="0">
</form></td>
<td>($objection_left_count)</td>

EOF;
  }
  echo '</tr></table>'."\n";

  if(($day_night == 'day' || $day_night == 'night') && $left_time == 0){
    echo '<div class="system-vote">' . $time_message . $MESSAGE->vote_announce .'</div>'."\n";
  }
}

//ŷ������å�����
function OutputHeavenTalkLog(){
  global $room_no, $game_option, $heaven_mode, $date, $day_night;

  //���Ͼ�������å�
  // global $uname, $live, $role;
  // if($live != 'dead') return false; //�ƤӽФ�¦�ǥ����å�����ΤǸ��ߤ�����

  //���äΥ桼��̾���ϥ�ɥ�̾��ȯ����ȯ���Υ����פ����
  $sql = mysql_query("SELECT user_entry.uname AS talk_uname,
			user_entry.handle_name AS talk_handle_name,
			user_entry.live AS talk_live,
			user_entry.sex AS talk_sex,
			user_icon.color AS talk_color,
			talk.sentence AS sentence,
			talk.font_type AS font_type,
			talk.location AS location
			FROM user_entry, talk, user_icon
			WHERE talk.room_no = $room_no
			AND talk.location LIKE 'heaven'
			AND ( (user_entry.room_no = $room_no AND user_entry.uname = talk.uname
			AND user_entry.icon_no = user_icon.icon_no)
			OR (user_entry.room_no = 0 AND talk.uname = 'system'
			AND user_entry.icon_no = user_icon.icon_no) )
			ORDER BY time DESC");

  $count = mysql_num_rows($sql);

  echo '<table class="talk">'."\n";
  for($i=0; $i < $count; $i++){
    $array = mysql_fetch_assoc($sql);
    $talk_uname       = $array['talk_uname'];
    $talk_handle_name = $array['talk_handle_name'];
    $talk_live        = $array['talk_live'];
    $talk_sex         = $array['talk_sex'];  //����̤����
    $talk_color       = $array['talk_color'];
    $sentence         = $array['sentence'];
    $font_type        = $array['font_type'];
    $location         = $array['location']; //����̤����

    LineToBR($sentence); //���Ԥ�<br>�������ִ�

    //����򿦤���������Ƥ�����Τ� HN ���ɲ�
    if(strpos($game_option, 'not_open_cast') === false){
      $talk_handle_name .= '<span>(' . $talk_uname . ')</span>';
    }

    //���ý���
    echo '<tr class="user-talk">'."\n";
    echo '<td class="user-name"><font color="' . $talk_color . '">��</font>' .
      $talk_handle_name . '</td>'."\n";
    echo '<td class="say ' . $font_type . '">' . $sentence . '</td>'."\n";
    echo '</tr>'."\n";
  }
  echo '</table>'."\n";
}

//ǽ�Ϥμ���Ȥ������������
function OutputAbility(){
  global $ROLE_IMG, $MESSAGE, $room_no, $date, $day_night, $uname, $handle_name, $user_no, $role, $live;

  //���Ͼ�������å�
  if($day_night == 'beforegame' || $day_night == 'aftergame') return false;

  if($live == 'dead'){ //��˴������ǽ�Ϥ�ɽ�����ʤ�
    echo '<span class="ability-dead">' . $MESSAGE->ability_dead . '</span><br>';
    return;
  }

  $yesterday = $date - 1;
  if(strpos($role, 'human') !== false)
    echo '<img src="' . $ROLE_IMG->human . '"><br>'."\n";
  elseif(strpos($role, 'wolf') !== false){
    echo '<img src="' . $ROLE_IMG->wolf . '"><br>'."\n";

    //��֤�ɽ��
    $sql = mysql_query("SELECT handle_name FROM user_entry WHERE room_no = $room_no
			AND role LIKE 'wolf%' AND uname <> '$uname' AND user_no > 0");
    $count = mysql_num_rows($sql);
    if($count > 0){
      echo '<table class="ability-partner"><tr>'."\n";
      echo '<td><img src="' . $ROLE_IMG->wolf_partner . '"></td>'."\n";
      echo '<td>��';
      for($i=0; $i < $count; $i++) echo mysql_result($sql, $i, 0) . '���󡡡�';
      echo '</td>'."\n".'</tr></table>'."\n";
    }

    if($day_night == 'night'){ //��γ�����ɼ
      $sql = mysql_query("SELECT uname FROM vote WHERE room_no = $room_no AND situation = 'WOLF_EAT'");
      if(mysql_num_rows($sql) == 0){
	echo '<span class="ability-wolf-eat">' . $MESSAGE->ability_wolf_eat . '</span><br>'."\n";
      }
    }
  }
  elseif(strpos($role, 'mage') !== false){
    echo '<img src="' . $ROLE_IMG->mage . '"><br>'."\n";

    //�ꤤ��̤�ɽ��
    $sql = mysql_query("SELECT message FROM system_message WHERE room_no = $room_no
			AND date = $yesterday AND type = 'MAGE_RESULT'");
    $count = mysql_num_rows($sql);
    for($i=0; $i < $count; $i++){
      list($mage, $target, $target_role) = ParseStrings(mysql_result($sql, $i, 0), 'MAGE_RESULT');

      if($handle_name == $mage){ //��ʬ���ꤤ��̤Τ�ɽ��
	echo '<table class="ability-result"><tr>'."\n";
	echo '<td><img src="' . $ROLE_IMG->mage_result . '"></td>'."\n";
	echo '<td>' . $target . '</td>';
	echo '<td><img src="' .
	  ($target_role == 'human' ? $ROLE_IMG->result_human : $ROLE_IMG->result_wolf) .
	  '"></td>'."\n";
	echo '</tr></table>'."\n";
      }
    }

    if($day_night == 'night'){ //����ꤤ��ɼ
      $sql_mage_voted = mysql_query("SELECT uname FROM vote WHERE room_no = $room_no
					AND uname = '$uname' AND situation = 'MAGE_DO'");
      if(mysql_num_rows($sql_mage_voted) == 0){
	echo '<span class="ability-mage-do">' . $MESSAGE->ability_mage_do . '</span><br>'."\n";
      }
    }
  }
  elseif(strpos($role, 'necromancer') !== false){
    echo '<img src="' . $ROLE_IMG->necromancer . '"><br>'."\n";

    //��ǽ��̤�ɽ��
    $sql = mysql_query("SELECT message FROM system_message WHERE room_no = $room_no
			AND date = $yesterday AND type = 'NECROMANCER_RESULT'");
    $count = mysql_num_rows($sql);
    for($i=0; $i < $count; $i++){
      list($target, $target_role) = ParseStrings(mysql_result($sql, $i, 0));
      echo '<table class="ability-result"><tr>'."\n";
      echo '<td><img src="' . $ROLE_IMG->necromancer_result . '"></td>'."\n";
      echo '<td>' . $target . '</td>';
      echo '<td><img src="' .
	($target_role == 'human' ? $ROLE_IMG->result_human : $ROLE_IMG->result_wolf) .
	'"></td>'."\n";
      echo '</tr></table>'."\n";
    }
  }
  elseif(strpos($role, 'mad') !== false)
    echo '<img src="' . $ROLE_IMG->mad . '"><br>'."\n";
  elseif(strpos($role, 'guard') !== false){
    echo '<img src="' . $ROLE_IMG->guard . '"><br>'."\n";

    //��ҷ�̤�ɽ��
    $sql = mysql_query("SELECT message FROM system_message WHERE room_no = $room_no
			AND date = $yesterday and type = 'GUARD_SUCCESS'");
    $count = mysql_num_rows($sql);
    for($i=0; $i < $count; $i++){
      list($guard, $target) = ParseStrings(mysql_result($sql, $i, 0));
      if($handle_name == $guard){ //��ʬ�θ�ҷ�̤Τ�
	echo '<table class="ability-result"><tr>'."\n";
	echo '<td>' . $target . '</td>';
	echo '<td><img src="' . $ROLE_IMG->guard_success . '"></td>'."\n";
	echo '</tr></table>'."\n";
      }
    }

    if($day_night == 'night' && $date != 1){ //��θ����ɼ
      $sql = mysql_query("SELECT uname FROM vote WHERE room_no = $room_no AND uname = '$uname'
				AND situation = 'GUARD_DO'");
      if(mysql_num_rows($sql) == 0){
	echo '<span class="ability-guard-do">' . $MESSAGE->ability_guard_do . '</span><br>'."\n";
      }
    }
  }
  elseif(strpos($role, 'common') !== false){
    echo '<img src="' . $ROLE_IMG->common . '"><br>'."\n";

    //��֤�ɽ��
    $sql = mysql_query("SELECT handle_name FROM user_entry WHERE room_no = $room_no
			AND role LIKE 'common%' AND uname <> '$uname' AND user_no > 0");
    $count = mysql_num_rows($sql);
    if($count > 0){
      echo '<table class="ability-partner"><tr>'."\n";
      echo '<td><img src="' . $ROLE_IMG->common_partner . '"></td>'."\n";
      echo '<td>��';
      for($i=0; $i < $count; $i++) echo mysql_result($sql, $i, 0) . '���󡡡�';
      echo '</td>'."\n".'</tr></table>'."\n";
    }
  }
  elseif(strpos($role, 'fox') !== false){
    echo '<img src="' . $ROLE_IMG->fox . '"><br>'."\n";

    //�Ѥ�����줿��å�������ɽ��
    $sql = mysql_query("SELECT message FROM system_message WHERE room_no = $room_no
			AND date = $yesterday AND type = 'FOX_EAT'");
    $count = mysql_num_rows($sql);
    for($i=0; $i < $count; $i++){
      if($handle_name == mysql_result($sql, $i, 0)){ //��ʬ������줿���Τ�
	echo '<table class="ability-result"><tr>'."\n";
	echo '<td><img src="' . $ROLE_IMG->fox_target . '"></td>'."\n";
	echo '</tr></table>'."\n";
      }
    }
  }
  elseif(strpos($role, 'poison') !== false)
    echo '<img src="' . $ROLE_IMG->poison . '"><br>'."\n";
  elseif(strpos($role, 'cupid') !== false){
    echo '<img src="' . $ROLE_IMG->cupid . '"><br>'."\n";

    //��ʬ������Ǥä����� (��ʬ���ȴޤ�) ��ɽ������
    $str_user_no = strval($user_no);
    $sql = mysql_query("SELECT handle_name FROM user_entry WHERE room_no = $room_no
 			AND role LIKE '%lovers[$str_user_no]%' AND user_no > 0");
    $count = mysql_num_rows($sql);
    if($count > 0){
      echo '<table class="ability-partner"><tr>'."\n";
      echo '<td><img src="' . $ROLE_IMG->cupid_pair . '"></td>'."\n";
      echo '<td>��';
      for($i=0; $i < $count; $i++) echo mysql_result($sql, $i, 0) . '���󡡡�';
      echo '</td>'."\n".'</tr></table>'."\n";
    }

    if($day_night == 'night' && $date == 1){ //���������ɼ
      $sql = mysql_query("SELECT uname FROM vote WHERE room_no = $room_no AND uname = '$uname'
				AND situation = 'CUPID_DO'");
      if(mysql_num_rows($sql) == 0){
	echo '<span class="ability-cupid-do">' . $MESSAGE->ability_cupid_do . '</span><br>'."\n";
      }
    }
  }

  //���������Ǥ��
  if(strpos($role, 'authority') !== false) echo '<img src="' . $ROLE_IMG->authority . '"><br>'."\n";
  // if(strpos($role, 'decide') !== false) echo '<img src="' . $ROLE_IMG->human . '"><br>'."\n";
  if(strpos($role, 'lovers') !== false){
    //���ͤ�ɽ������
    $lovers_str = GetLoversConditionString($role);
    $sql = mysql_query("SELECT handle_name FROM user_entry WHERE room_no = $room_no
 			AND $lovers_str AND uname <> '$uname' AND user_no > 0");
    $count = mysql_num_rows($sql);
    if($count > 0){
      echo '<table class="ability-partner"><tr>'."\n";
      echo '<td><img src="' . $ROLE_IMG->lovers_header . '"></td>'."\n";
      echo '<td>��';
      for($i=0; $i < $count; $i++) echo mysql_result($sql, $i, 0) . '���󡡡�';
      echo '</td>'."\n";
      echo '<td><img src="' . $ROLE_IMG->lovers_footer . '"></td>'."\n";
      echo '</tr></table>'."\n";
    }
  }
}

//��ʬ��̤��ɼ�����å�
function CheckSelfVote(){
  global $room_no, $date, $uname;

  //��ɼ��������(����ɼ�ʤ� $vote_times ��������)
  $sql = mysql_query("SELECT message FROM system_message WHERE room_no = $room_no
			AND type = 'VOTE_TIMES' AND date = $date");
  $vote_times = (int)mysql_result($sql, 0, 0);
  echo '<div class="self-vote">��ɼ ' . $vote_times . ' ���ܡ�';

  //��ɼ�Ѥߤ��ɤ���
  $sql = mysql_query("SELECT COUNT(uname) FROM vote WHERE room_no = $room_no
			AND uname = '$uname' AND date = $date AND vote_times = $vote_times
			AND situation = 'VOTE_KILL'");
  echo (mysql_result($sql, 0, 0) ? '��ɼ�Ѥ�' : '�ޤ���ɼ���Ƥ��ޤ���') . '</div>'."\n";
}

//��ʬ�ΰ�������
function OutputSelfLastWords(){
  global $room_no, $day_night, $uname;

  //���Ͼ�������å�
  if($day_night == 'aftergame') return false;

  $sql = mysql_query("SELECT last_words FROM user_entry WHERE room_no = $room_no
			AND uname = '$uname' AND user_no > 0");

  //�ޤ����Ϥ��Ƥʤ����ɽ�����ʤ�
  if(mysql_num_rows($sql) == 0) return false;

  $last_words = mysql_result($sql, 0, 0);
  LineToBR($last_words);
  if($last_words == '') return false;

  echo <<<EOF
<table class="lastwords" cellspacing="5"><tr>
<td class="lastwords-title">��ʬ�ΰ��</td>
<td class="lastwords-body">{$last_words}</td>
</tr></table>

EOF;
}
?>
