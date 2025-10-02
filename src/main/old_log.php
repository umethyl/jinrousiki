<?php require_once(dirname(__FILE__) . '/include/game_functions.php');
//����No����
$room_no  = (int)$_GET['room_no'];
$log_mode    = $_GET['log_mode'];
$reverse_log = $_GET['reverse_log'];
$heaven_talk = $_GET['heaven_talk'];
$heaven_only = $_GET['heaven_only'];
$page        = (int)$_GET['page'];

$dbHandle = ConnectDatabase(); //DB ��³

switch($log_mode){
  case 'on':
    OutputOldLog($room_no);
    break;

  default:
    OutputFinishedRooms($page, $_GET['reverse']);
    break;
}

DisconnectDatabase($dbHandle); //DB ��³���
OutputHTMLFooter();

// �ؿ� //
//��������ɽ��
function OutputFinishedRooms($page, $reverse = NULL){
  global $ROOM_IMG, $VICTORY_IMG, $DEBUG_MODE;

  //¼���γ�ǧ
  $sql = mysql_query("SELECT COUNT(*) FROM room WHERE status = 'finished'");
  $num_rooms = mysql_result($sql, 0);
  if($num_rooms == 0){
    OutputActionResult('��Ͽ�ϵ�ʤ�䡩[����]', '���Ϥ���ޤ���<br>' . "\n" .
		       '<a href="index.php">�����</a>'."\n");
  }

  OutputHTMLHeader('��Ͽ�ϵ�ʤ��?[��������]', 'old_log_list');
echo <<<EOF
<body id="room_list">
<p><a href="index.php">�����</a></p>
<img src="img/old_log_title.jpg"><br>
<div align="center">
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td align="right">
[�ڡ���]

EOF;

  $config = new OldLogConfig(); //��������
  if($reverse == NULL) $reverse = $config->reverse ? 'on' : 'off';

  //�ڡ�����󥯤ν���
  if($page == NULL) $page = 1;
  $num_pages = ceil($num_rooms / $config->one_page) + 1; //[all] �ΰ٤� + 1 ���Ƥ���
  $reverse_option = ($reverse == 'on' ? 'on' : 'off');
  for($page_number = 1; $page_number <= $num_pages; $page_number++){
    $page_title = $page_number == $num_pages ? 'all' : $page_number;
    if($page != $page_title){
      echo " <a href=\"old_log.php?page=$page_title&reverse=$reverse_option\">[$page_title]</a> ";
    }
    else{
      echo " [$page_title] ";
    }
  }
  if($reverse == 'on')
    echo 'ɽ����:������ <a href="old_log.php?reverse=off">�����᤹</a>';
  else
    echo 'ɽ����:�Ţ��� <a href="old_log.php?reverse=on">�����ؤ���</a>';

  echo <<<EOF
</td></tr>
<!--¼���� ��������-->
<tr><td>
<table border="1" align="center" cellspacing="1" bgcolor="#CCCCCC">
<tr class="column"><th>¼No</th><th>¼̾</th><th>¼�ˤĤ���</th><th colspan="2">�Ϳ�</th><th>��</th><th colspan="9">���ץ����</th></tr>

EOF;

  //����ɽ���ξ�硢��ڡ���������ɽ�����롣����ʳ������ꤷ��������ɽ��
  if($page == 'all'){
    $limit_statement = '';
  }
  else{
    $start_number = $config->one_page * ($page - 1);
    $limit_statement = sprintf('LIMIT %d, %d', $start_number, $config->one_page);
  }

  //ɽ������Ԥμ���
  $room_order = ($reverse == 'on' ? 'DESC' : '');
  $res_oldlog_list = mysql_query("
    SELECT
      room_no,
      room_name,
      room_comment,
      date AS room_date,
      game_option AS room_game_option,
      option_role AS room_option_role,
      max_user AS room_max_user,
      (SELECT COUNT(*) FROM user_entry user WHERE user.room_no = room.room_no AND user.user_no > 0) AS room_num_user,
      victory_role AS room_victory_role
    FROM room 
    WHERE status = 'finished' 
    ORDER BY room_no $room_order 
    $limit_statement"
  );
  while (($oldlog_list_arr = mysql_fetch_assoc($res_oldlog_list)) !== false){
    extract($oldlog_list_arr, EXTR_PREFIX_ALL, 'log');
    //���ץ����Ⱦ��Ԥβ���
    if(strpos($log_room_game_option,'wish_role') !== false)
      $log_wish_role_str = $ROOM_IMG->GenerateTag('wish_role', '����˾��');
    else
      $log_wish_role_str = "<br>";

    if(strpos($log_room_game_option, 'real_time') !== false){
      if(strpos($log_room_game_option, 'real_time:' !== false)){
        //�»��֤����»��֤����
        $real_time_str = strstr($log_room_game_option, 'real_time');
        sscanf($real_time_str,"real_time:%d:%d",&$day_real_limit_minutes,&$night_real_limit_minutes);
        $real_time_alt_str = "�ꥢ�륿���������롧 $day_real_limit_minutes ʬ���롧 $night_real_limit_minutes ʬ";
      }
      else {
        $real_time_alt_str = "�ꥢ�륿������";
      }
      $log_real_time_str = $ROOM_IMG->GenerateTag('real_time', $real_time_alt_str);
    }
    else
      $log_real_time_str = "<br>";

    if(strpos($log_room_game_option,"dummy_boy") !== false)
      $log_dummy_boy_str = $ROOM_IMG->GenerateTag('dummy_boy', '��������Ͽ����귯');
    else
      $log_dummy_boy_str = "<br>";

    if(strpos($log_room_game_option,"open_vote") !== false)
      $log_open_vote_str = $ROOM_IMG->GenerateTag('open_vote', '��ɼ����ɼ�����ɽ����');
    else
      $log_open_vote_str = "<br>";

    if(strpos($log_room_game_option,"not_open_cast") !== false)
      $log_not_open_cast_str = $ROOM_IMG->GenerateTag('not_open_cast', '���������ɽ���ʤ�');
    else
      $log_not_open_cast_str = "<br>";

    if(strpos($log_room_option_role,"decide") !== false)
      $log_decide_str = $ROOM_IMG->GenerateTag('decide', '16�Ͱʾ�Ƿ�����о�');
    else
      $log_decide_str = "<br>";

    if(strpos($log_room_option_role,"authority") !== false)
      $log_authority_str = $ROOM_IMG->GenerateTag('authority', '16�Ͱʾ�Ǹ��ϼ��о�');
    else
      $log_authority_str = "<br>";

    if(strpos($log_room_option_role,"poison") !== false)
      $log_poison_str = $ROOM_IMG->GenerateTag('poison', '20�Ͱʾ�����Ǽ��о�');
    else
      $log_poison_str = "<br>";

    if(strpos($log_room_option_role,"cupid") !== false)
      $log_cupid_str = $ROOM_IMG->GenerateTag('cupid', '14�͡��ޤ���16�Ͱʾ�ǥ��塼�ԥå��о�');
    else
      $log_cupid_str = "<br>";

    switch($log_room_victory_role){
      case 'human':
        $voctory_role_str = $VICTORY_IMG->GenerateTag('human', '¼�;���', 'winner');
	break;
      case 'wolf':
        $voctory_role_str = $VICTORY_IMG->GenerateTag('wolf', '��ϵ����', 'winner');
	break;
	// case 'fox': //���ߤ� fox1 or fox2 �ΤߤʤΤ�����
      case 'fox1':
      case 'fox2':
        $voctory_role_str = $VICTORY_IMG->GenerateTag('fox', '�ŸѾ���', 'winner');
	break;
      case 'lovers':
        $voctory_role_str = $VICTORY_IMG->GenerateTag('lovers', '���;���', 'winner');
	break;
      case 'draw':
      case 'vanish':
        $voctory_role_str = $VICTORY_IMG->GenerateTag('draw', '����ʬ��', 'winner');
	break;
      default:
	$voctory_role_str = "-";
	break;
    }

    if($log_room_date == 0) //��¼�ξ�硢���򳥿��ˤ���
      $dead_room_color = ' style="color:silver"';
    else
      $dead_room_color = '';

    //�桼����������
    $str_max_users = $ROOM_IMG->max_user_list[$log_room_max_user];
    $user_count = (int)$log_room_num_user;

    if ($DEBUG_MODE){
      $debug_anchor = "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&debug=on\" $dead_room_color >Ͽ</a>";
    }

    echo <<<ROOM_ROW
<tr>
<td align=right valign=middle class=row>$log_room_no</td> 
<td align=right valign=middle class=row> 
<a href="old_log.php?log_mode=on&room_no=$log_room_no" $dead_room_color >$log_room_name ¼</a>
<small>(<a href="old_log.php?log_mode=on&room_no=$log_room_no&reverse_log=on" $dead_room_color >��</a>
<a href="old_log.php?log_mode=on&room_no=$log_room_no&heaven_talk=on" $dead_room_color >��</a>
<a href="old_log.php?log_mode=on&room_no=$log_room_no&reverse_log=on&heaven_talk=on" $dead_room_color >��&amp;��</a>
<a href="old_log.php?log_mode=on&room_no=$log_room_no&heaven_only=on" $dead_room_color ><small>��</small></a>
<a href="old_log.php?log_mode=on&room_no=$log_room_no&reverse_log=on&heaven_only=on" $dead_room_color ><small>��&amp;��</small></a>
$debug_anchor
)</small></td> 
<td align="right" valign="middle" class="row"><small>�� $log_room_comment ��</small></td> 
<td align="center" valign="middle" class="row"><img src="$str_max_users"></td> 
<td align="center" valign="middle" class="row">$user_count</td> 
<td align="center" valign="middle" class="row">$voctory_role_str</td> 
<td valign="middle" width="16" class="row">$log_wish_role_str </td> 
<td valign="middle" width="16" class="row">$log_real_time_str </td> 
<td valign="middle" width="16" class="row">$log_dummy_boy_str </td> 
<td valign="middle" width="16" class="row">$log_open_vote_str </td> 
<td valign="middle" width="16" class="row">$log_not_open_cast_str </td> 
<td valign="middle" width="16" class="row">$log_decide_str </td> 
<td valign="middle" width="16" class="row">$log_authority_str </td> 
<td valign="middle" width="16" class="row">$log_poison_str </td> 
<td valign="middle" width="16" class="row">$log_cupid_str </td> 
</tr>

ROOM_ROW;
  }
  echo <<<FOOTER
</table>
</td></tr>
</table>
</div>

FOOTER;
}


//���������No�Υ�����Ϥ���
function OutputOldLog($room_no){
  global $reverse_log, $heaven_only, $status, $day_night, $last_date, $live;

  $base_title = '��Ͽ�ϵ�ʤ�䡩[����]';
  $url   = '<br>' . "\n" . '<a href="old_log.php">�����</a>'."\n";

  if($room_no == NULL) OutputActionResult($title, '¼����ꤷ�Ƥ���������' . $url);

  //���դȥ���������
  $sql = mysql_query("SELECT date, day_night, room_name, room_comment, status
			FROM room WHERE room_no = $room_no");
  $array = mysql_fetch_assoc($sql);
  static $last_date; $last_date   = $array['date'];
  $day_night    = $array['day_night'];
  $room_name    = $array['room_name'];
  $room_comment = $array['room_comment'];
  $status       = $array['status'];

  if($status != 'finished' || $day_night != 'aftergame'){
    OutputActionResult($base_title, '�ޤ����������Υ��ϱ����Ǥ��ޤ���' . $url);
  }

  $live = 'dead'; //¾�δؿ��˱ƶ������٤�ɽ�����뤿��
  $title = '[' . $room_no . '����]' . $room_name . ' - ' . $base_title;

  //���������Υڡ����ˤ���
  $referer_page_str = strstr($_SERVER['HTTP_REFERER'], 'page');
  sscanf($referer_page_str, "page=%s", &$referer_page);

  OutputHTMLHeader($title, 'old_log');
  echo <<<EOF
<a href="old_log.php?page=$referer_page">�����</a><br>
<div class="room"><span>{$room_name}¼</span>����{$room_comment}�� [{$room_no}����]</td></div>

EOF;
  OutputPlayerList();   //�ץ쥤�䡼�ꥹ�Ȥ����

  if($reverse_log == 'on'){ //�ս�ɽ���������ܤ���ǽ����ޤ�
    if($heaven_only == 'on'){
      for($i=1; $i <= $last_date; $i++) OutputDateTalkLog($i, 'heaven_only');
    }
    else{
      OutputDateTalkLog($date, 'beforegame');
      for($i=1; $i <= $last_date; $i++) OutputDateTalkLog($i, '');
      OutputVictory();
      OutputDateTalkLog($date, 'aftergame');
    }
  }
  else{ //�ǽ�������ǽ�ޤ�
    if($heaven_only == 'on'){
      for($i = $last_date; $i > 0; $i--) OutputDateTalkLog($i, 'heaven_only');
    }
    else{
      OutputDateTalkLog($date, 'aftergame');
      OutputVictory();
      for($i = $last_date; $i > 0; $i--) OutputDateTalkLog($i, '');
      OutputDateTalkLog($date,'beforegame');
    }
  }
}

//��������դβ��å������
function OutputDateTalkLog($set_date, $set_location){
  global $reverse_log, $heaven_talk, $heaven_only, $room_no, $status, $date, $day_night, $live;

  if($reverse_log == 'on') //�ս硢��������ǽ����ޤ�
    $select_order = 'ORDER BY time';
  else //�ǽ�����������ޤ�
    $select_order = 'ORDER BY time DESC';

  if($set_location == 'heaven_only'){
    //���äΥ桼��̾���ϥ�ɥ�̾��ȯ����ȯ���Υ����פ����
    $sql = mysql_query("SELECT user_entry.uname AS talk_uname,
			user_entry.handle_name AS talk_handle_name,
			user_entry.sex AS talk_sex,
			user_icon.color AS talk_color,
			talk.sentence AS sentence,
			talk.font_type AS font_type,
			talk.location AS location
			FROM user_entry, talk, user_icon
			WHERE talk.room_no = $room_no
			AND talk.date = $set_date
			AND ( (talk.location = 'heaven') OR (talk.uname = 'system') )
			AND ( (user_entry.room_no = $room_no AND user_entry.uname = talk.uname
			AND user_entry.icon_no = user_icon.icon_no)
			OR ( user_entry.room_no = 0 AND talk.uname = 'system'
			AND user_entry.icon_no = user_icon.icon_no) )
			$select_order");
    if($reverse_log == 'on' && $set_date != 1) //�����ܰʹߤ��뤫��Ϥޤ�
      $table_class = 'day';
    else
      $table_class = 'night';
  }
  elseif($set_location == 'beforegame' || $set_location == 'aftergame'){
    //���äΥ桼��̾���ϥ�ɥ�̾��ȯ����ȯ���Υ����פ����
    $sql = mysql_query("SELECT user_entry.uname AS talk_uname,
			user_entry.handle_name AS talk_handle_name,
			user_entry.sex AS talk_sex,
			user_icon.color AS talk_color,
			talk.sentence AS sentence,
			talk.font_type AS font_type,
			talk.location AS location
			FROM user_entry, talk, user_icon
			WHERE talk.room_no = $room_no
			AND talk.location like '$set_location%'
			AND ( (user_entry.room_no = $room_no AND user_entry.uname = talk.uname
			AND user_entry.icon_no = user_icon.icon_no)
			OR (user_entry.room_no = 0 AND talk.uname = 'system'
			AND user_entry.icon_no = user_icon.icon_no) )
			$select_order");
    $table_class = $set_location;
  }
  else{
    $hide_heaven_query = ($heaven_talk == 'on') ? "" : "AND talk.location <> 'heaven'";
    //���äΥ桼��̾���ϥ�ɥ�̾��ȯ����ȯ���Υ����פ����
    $sql = mysql_query("SELECT user_entry.uname AS talk_uname,
			user_entry.handle_name AS talk_handle_name,
			user_entry.sex AS talk_sex,
			user_icon.color AS talk_color,
			talk.sentence AS sentence,
			talk.font_type AS font_type,
			talk.location AS location
			FROM user_entry, talk, user_icon
			WHERE talk.room_no = $room_no
			AND talk.date = $set_date
			AND talk.location <> 'aftergame'
			AND talk.location <> 'beforegame'
			$hide_heaven_query
			AND ( (user_entry.room_no = $room_no AND user_entry.uname = talk.uname
			AND user_entry.icon_no = user_icon.icon_no)
			OR (user_entry.room_no = 0 AND talk.uname = 'system'
			AND user_entry.icon_no = user_icon.icon_no) )
			$select_order");

    if($reverse_log == 'on' && $set_date != 1) //�����ܰʹߤ��뤫��Ϥޤ�
      $table_class = 'day';
    else
      $table_class = 'night';
  }
  $talk_count = mysql_num_rows($sql);
  //print(mysql_result($sql,1,6));

  if($set_location != 'beforegame' && $set_location != 'aftergame' &&
     $set_date != $last_date && $reverse_log != 'on' && $heaven_only != 'on'){
    $date = $set_date + 1;
    $day_night = 'day';
    OutputLastWords(); //��������
    OutputDeadMan();   //��˴�Ԥ����
  }
  $day_night = $table_class;

  //����
  echo '<table class="old-log-talk ' . $table_class . '">'."\n";
  for($i = 0; $i < $talk_count; $i++){
    $array = mysql_fetch_assoc($sql);
    $location = $array['location'];
    if(strpos($location, 'day') !== false && $day_night != 'day'){
      OutputSceneChange($set_date);
      $day_night = 'day';
      echo '<table class="old-log-talk ' . $day_night . '">'."\n";
    }
    elseif(strpos($location, 'night') !== false && $day_night != 'night'){
      OutputSceneChange($set_date);
      $day_night = 'night';
      echo '<table class="old-log-talk ' . $day_night . '">'."\n";
    }
    OutputTalk($array); //���ý���
  }
  echo '</table>';

  if($set_location != 'beforegame' && $set_location != 'aftergame' &&
     $set_date != $last_date && $reverse_log == 'on' && $heaven_only != 'on'){
    $day_night = 'day';
    $date = $set_date + 1;
    OutputDeadMan();   //��˴�Ԥ����
    OutputLastWords(); //��������
  }
}

//�������ڤ��ؤ����Υ�����
function OutputSceneChange($set_date){
  global $reverse_log, $heaven_only, $date, $day_night;

  echo '</table>'."\n";
  if($heaven_only == 'on') return;
  $date = $set_date;
  if($reverse_log == 'on'){
    OutputVoteList(); //��ɼ��̽���
    $day_night = 'night';
    OutputDeadMan();  //��˴�Ԥ����
  }
  else{
    OutputDeadMan();  //��˴�Ԥ����
    OutputVoteList(); //��ɼ��̽���
  }
}
?>
