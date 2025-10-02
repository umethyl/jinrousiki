<?php
require_once(dirname(__FILE__) . '/include/game_functions.php');

//���������
$room_no     = (int)$_GET['room_no']; //���� No
$auto_reload = (int)$_GET['auto_reload']; //�����ȥ���ɤδֳ�
if($auto_reload != 0 && $auto_reload < $GAME_CONF->auto_reload_list[0])
  $auto_reload = $GAME_CONF->auto_reload_list[0];

$view_mode = 'on';
$url = 'game_view.php?room_no=' . $room_no . '&view_mode=on';

$dbHandle = ConnectDatabase(); // DB ��³

//���դȥ���������
$sql = mysql_query("SELECT date, day_night, room_name, room_comment, game_option
			FROM room WHERE room_no = $room_no");
$array = mysql_fetch_assoc($sql);
$date         = $array['date'];
$day_night    = $array['day_night'];
$room_name    = $array['room_name'];
$room_comment = $array['room_comment'];
$game_option  = $array['game_option'];
$real_time    = (strpos($game_option, 'real_time') !== false);
$system_time  = TZTime(); //���߻�������
switch($day_night){
  case 'day': //��
    $time_message = '�����פޤ� ';
    break;

  case 'night': //��
    $time_message = '���������ޤ� ';
    break;
}

OutputHTMLHeader('��Ͽ�ϵ�ʤ�䡩[����]', 'game_view'); //HTML�إå�

if($GAME_CONF->auto_reload && $auto_reload != 0) //��ư����
  echo '<meta http-equiv="Refresh" content="' . $auto_reload . '">'."\n";

//������˹�碌��ʸ�������طʿ� CSS �����
echo '<link rel="stylesheet" href="css/game_' . $day_night . '.css">'."\n";

//�в���֤����
if($real_time){ //�ꥢ�륿������
  list($start_time, $end_time) = GetRealPassTime($left_time, true);
  if($day_night == 'day' || $day_night == 'night'){
    $on_load = ' onLoad="output_realtime();"';
    OutputRealTimer($start_time, $end_time);
  }
}
else{ //���äǻ��ַв���
  $left_talk_time = GetTalkPassTime($left_time);
}

echo <<<EOF
</head>
<body{$on_load}>
<table class="login" id="game_top"><tr>
<td classs="room"><span>{$room_name}¼</span>����{$room_comment}��[{$room_no}����]</td>
<td class="login-link">

EOF;

if($GAME_CONF->auto_reload){ //��ư�������꤬ͭ���ʤ��󥯤�ɽ��
  echo '<a href="' . $url . '&auto_reload=' . $auto_reload . '">[����]</a>'."\n";
  OutputAutoReloadLink('<a href="' . $url . '&auto_reload=');
}
else{
  echo '<a href="' . $url . '">[����]</a>'."\n";
}

echo <<<EOF
<a href="index.php">[���]</a>
</td></tr>
<tr><td><form method="POST" action="login.php?room_no=$room_no">
<label>�桼��̾</label><input type="text" name="uname" size="20">
<label>�ѥ����</label><input type="password" class="login-password" name="password" size="20">
<input type="hidden" name="login_type" value="manually">
<input type="submit" value="������">
</form></td>

EOF;

if($day_night == 'beforegame'){
  echo '<td class="login-link">';
  echo '<a href="user_manager.php?room_no=' . $room_no . '"><span>[��̱��Ͽ]</span></a>';
  echo '</td>'."\n";
}
echo '</tr></table>'."\n";

echo '<table class="time-table"><tr>'."\n";
OutputTimeTable(); //�в���������¸�Ϳ�

if($day_night == 'day' || $day_night == 'night'){
  if($real_time){ //�ꥢ�륿������
    echo '<td class="real-time"><form name="realtime_form">'."\n";
    echo '<input type="text" name="output_realtime" size="50" readonly>'."\n";
    echo '</form></td>'."\n";
  }
  elseif($left_time){ //ȯ���ˤ�벾�ۻ���
    echo '<td>' . $time_message . $left_talk_time . '</td>'."\n";
  }

  if($left_time == 0){
    echo '</tr><tr>'."\n" . '<td class="system-vote" colspan="2">' . $time_message .
      $MESSAGE->vote_announce . '</td>'."\n";
  }
}
echo '</tr></table>'."\n";

OutputPlayerList(); //�ץ쥤�䡼�ꥹ��
if($day_night == 'aftergame') OutputVictory(); //���Է��
OutputReVoteList(); //����ɼ��å�����
OutputTalkLog();    //���å�
OutputLastWords();  //���
OutputDeadMan();    //��˴��
OutputVoteList();   //��ɼ���
OutputHTMLFooter(); //HTML�եå�

DisconnectDatabase($dbHandle); //DB ��³���
?>
