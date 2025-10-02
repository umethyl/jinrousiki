<?php require_once(dirname(__FILE__) . '/include/game_functions.php');
//部屋No取得
$room_no  = (int)$_GET['room_no'];
$log_mode    = $_GET['log_mode'];
$reverse_log = $_GET['reverse_log'];
$heaven_talk = $_GET['heaven_talk'];
$heaven_only = $_GET['heaven_only'];
$page        = (int)$_GET['page'];

$dbHandle = ConnectDatabase(); //DB 接続

switch($log_mode){
  case 'on':
    OutputOldLog($room_no);
    break;

  default:
    OutputFinishedRooms($page, $_GET['reverse']);
    break;
}

DisconnectDatabase($dbHandle); //DB 接続解除
OutputHTMLFooter();

// 関数 //
//過去ログ一覧表示
function OutputFinishedRooms($page, $reverse = NULL){
  global $ROOM_IMG, $VICTORY_IMG, $DEBUG_MODE;

  //村数の確認
  $sql = mysql_query("SELECT COUNT(*) FROM room WHERE status = 'finished'");
  $num_rooms = mysql_result($sql, 0);
  if($num_rooms == 0){
    OutputActionResult('汝は人狼なりや？[過去ログ]', 'ログはありません。<br>' . "\n" .
		       '<a href="index.php">←戻る</a>'."\n");
  }

  OutputHTMLHeader('汝は人狼なりや?[過去ログ一覧]', 'old_log_list');
echo <<<EOF
<body id="room_list">
<p><a href="index.php">←戻る</a></p>
<img src="img/old_log_title.jpg"><br>
<div align="center">
<table border="0" cellpadding="0" cellspacing="0">
<tr>
<td align="right">
[ページ]

EOF;

  $config = new OldLogConfig(); //設定をロード
  if($reverse == NULL) $reverse = $config->reverse ? 'on' : 'off';

  //ページリンクの出力
  if($page == NULL) $page = 1;
  $num_pages = ceil($num_rooms / $config->one_page) + 1; //[all] の為に + 1 しておく
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
    echo '表示順:新↓古 <a href="old_log.php?reverse=off">元に戻す</a>';
  else
    echo '表示順:古↓新 <a href="old_log.php?reverse=on">入れ替える</a>';

  echo <<<EOF
</td></tr>
<!--村一覧 ここから-->
<tr><td>
<table border="1" align="center" cellspacing="1" bgcolor="#CCCCCC">
<tr class="column"><th>村No</th><th>村名</th><th>村について</th><th colspan="2">人数</th><th>勝</th><th colspan="9">オプション</th></tr>

EOF;

  //全部表示の場合、一ページで全部表示する。それ以外は設定した数ごと表示
  if($page == 'all'){
    $limit_statement = '';
  }
  else{
    $start_number = $config->one_page * ($page - 1);
    $limit_statement = sprintf('LIMIT %d, %d', $start_number, $config->one_page);
  }

  //表示する行の取得
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
    //オプションと勝敗の解析
    if(strpos($log_room_game_option,'wish_role') !== false)
      $log_wish_role_str = $ROOM_IMG->GenerateTag('wish_role', '役割希望制');
    else
      $log_wish_role_str = "<br>";

    if(strpos($log_room_game_option, 'real_time') !== false){
      if(strpos($log_room_game_option, 'real_time:' !== false)){
        //実時間の制限時間を取得
        $real_time_str = strstr($log_room_game_option, 'real_time');
        sscanf($real_time_str,"real_time:%d:%d",&$day_real_limit_minutes,&$night_real_limit_minutes);
        $real_time_alt_str = "リアルタイム制　昼： $day_real_limit_minutes 分　夜： $night_real_limit_minutes 分";
      }
      else {
        $real_time_alt_str = "リアルタイム制";
      }
      $log_real_time_str = $ROOM_IMG->GenerateTag('real_time', $real_time_alt_str);
    }
    else
      $log_real_time_str = "<br>";

    if(strpos($log_room_game_option,"dummy_boy") !== false)
      $log_dummy_boy_str = $ROOM_IMG->GenerateTag('dummy_boy', '初日の夜は身代わり君');
    else
      $log_dummy_boy_str = "<br>";

    if(strpos($log_room_game_option,"open_vote") !== false)
      $log_open_vote_str = $ROOM_IMG->GenerateTag('open_vote', '投票した票数を公表する');
    else
      $log_open_vote_str = "<br>";

    if(strpos($log_room_game_option,"not_open_cast") !== false)
      $log_not_open_cast_str = $ROOM_IMG->GenerateTag('not_open_cast', '霊界で配役を公表しない');
    else
      $log_not_open_cast_str = "<br>";

    if(strpos($log_room_option_role,"decide") !== false)
      $log_decide_str = $ROOM_IMG->GenerateTag('decide', '16人以上で決定者登場');
    else
      $log_decide_str = "<br>";

    if(strpos($log_room_option_role,"authority") !== false)
      $log_authority_str = $ROOM_IMG->GenerateTag('authority', '16人以上で権力者登場');
    else
      $log_authority_str = "<br>";

    if(strpos($log_room_option_role,"poison") !== false)
      $log_poison_str = $ROOM_IMG->GenerateTag('poison', '20人以上で埋毒者登場');
    else
      $log_poison_str = "<br>";

    if(strpos($log_room_option_role,"cupid") !== false)
      $log_cupid_str = $ROOM_IMG->GenerateTag('cupid', '14人、または16人以上でキューピッド登場');
    else
      $log_cupid_str = "<br>";

    switch($log_room_victory_role){
      case 'human':
        $voctory_role_str = $VICTORY_IMG->GenerateTag('human', '村人勝利', 'winner');
	break;
      case 'wolf':
        $voctory_role_str = $VICTORY_IMG->GenerateTag('wolf', '人狼勝利', 'winner');
	break;
	// case 'fox': //現在は fox1 or fox2 のみなので不要
      case 'fox1':
      case 'fox2':
        $voctory_role_str = $VICTORY_IMG->GenerateTag('fox', '妖狐勝利', 'winner');
	break;
      case 'lovers':
        $voctory_role_str = $VICTORY_IMG->GenerateTag('lovers', '恋人勝利', 'winner');
	break;
      case 'draw':
      case 'vanish':
        $voctory_role_str = $VICTORY_IMG->GenerateTag('draw', '引き分け', 'winner');
	break;
      default:
	$voctory_role_str = "-";
	break;
    }

    if($log_room_date == 0) //廃村の場合、色を灰色にする
      $dead_room_color = ' style="color:silver"';
    else
      $dead_room_color = '';

    //ユーザ総数を取得
    $str_max_users = $ROOM_IMG->max_user_list[$log_room_max_user];
    $user_count = (int)$log_room_num_user;

    if ($DEBUG_MODE){
      $debug_anchor = "<a href=\"old_log.php?log_mode=on&room_no=$log_room_no&debug=on\" $dead_room_color >録</a>";
    }

    echo <<<ROOM_ROW
<tr>
<td align=right valign=middle class=row>$log_room_no</td> 
<td align=right valign=middle class=row> 
<a href="old_log.php?log_mode=on&room_no=$log_room_no" $dead_room_color >$log_room_name 村</a>
<small>(<a href="old_log.php?log_mode=on&room_no=$log_room_no&reverse_log=on" $dead_room_color >逆</a>
<a href="old_log.php?log_mode=on&room_no=$log_room_no&heaven_talk=on" $dead_room_color >霊</a>
<a href="old_log.php?log_mode=on&room_no=$log_room_no&reverse_log=on&heaven_talk=on" $dead_room_color >逆&amp;霊</a>
<a href="old_log.php?log_mode=on&room_no=$log_room_no&heaven_only=on" $dead_room_color ><small>逝</small></a>
<a href="old_log.php?log_mode=on&room_no=$log_room_no&reverse_log=on&heaven_only=on" $dead_room_color ><small>逆&amp;逝</small></a>
$debug_anchor
)</small></td> 
<td align="right" valign="middle" class="row"><small>〜 $log_room_comment 〜</small></td> 
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


//指定の部屋Noのログを出力する
function OutputOldLog($room_no){
  global $reverse_log, $heaven_only, $status, $day_night, $last_date, $live;

  $base_title = '汝は人狼なりや？[過去ログ]';
  $url   = '<br>' . "\n" . '<a href="old_log.php">←戻る</a>'."\n";

  if($room_no == NULL) OutputActionResult($title, '村を指定してください。' . $url);

  //日付とシーンを取得
  $sql = mysql_query("SELECT date, day_night, room_name, room_comment, status
			FROM room WHERE room_no = $room_no");
  $array = mysql_fetch_assoc($sql);
  static $last_date; $last_date   = $array['date'];
  $day_night    = $array['day_night'];
  $room_name    = $array['room_name'];
  $room_comment = $array['room_comment'];
  $status       = $array['status'];

  if($status != 'finished' || $day_night != 'aftergame'){
    OutputActionResult($base_title, 'まだこの部屋のログは閲覧できません。' . $url);
  }

  $live = 'dead'; //他の関数に影響、すべて表示するため
  $title = '[' . $room_no . '番地]' . $room_name . ' - ' . $base_title;

  //戻る先を前のページにする
  $referer_page_str = strstr($_SERVER['HTTP_REFERER'], 'page');
  sscanf($referer_page_str, "page=%s", &$referer_page);

  OutputHTMLHeader($title, 'old_log');
  echo <<<EOF
<a href="old_log.php?page=$referer_page">←戻る</a><br>
<div class="room"><span>{$room_name}村</span>　〜{$room_comment}〜 [{$room_no}番地]</td></div>

EOF;
  OutputPlayerList();   //プレイヤーリストを出力

  if($reverse_log == 'on'){ //逆順表示、一日目から最終日まで
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
  else{ //最終日から最初まで
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

//指定の日付の会話ログを出力
function OutputDateTalkLog($set_date, $set_location){
  global $reverse_log, $heaven_talk, $heaven_only, $room_no, $status, $date, $day_night, $live;

  if($reverse_log == 'on') //逆順、初日から最終日まで
    $select_order = 'ORDER BY time';
  else //最終日から初日まで
    $select_order = 'ORDER BY time DESC';

  if($set_location == 'heaven_only'){
    //会話のユーザ名、ハンドル名、発言、発言のタイプを取得
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
    if($reverse_log == 'on' && $set_date != 1) //二日目以降は昼から始まる
      $table_class = 'day';
    else
      $table_class = 'night';
  }
  elseif($set_location == 'beforegame' || $set_location == 'aftergame'){
    //会話のユーザ名、ハンドル名、発言、発言のタイプを取得
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
    //会話のユーザ名、ハンドル名、発言、発言のタイプを取得
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

    if($reverse_log == 'on' && $set_date != 1) //二日目以降は昼から始まる
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
    OutputLastWords(); //遺言を出力
    OutputDeadMan();   //死亡者を出力
  }
  $day_night = $table_class;

  //出力
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
    OutputTalk($array); //会話出力
  }
  echo '</table>';

  if($set_location != 'beforegame' && $set_location != 'aftergame' &&
     $set_date != $last_date && $reverse_log == 'on' && $heaven_only != 'on'){
    $day_night = 'day';
    $date = $set_date + 1;
    OutputDeadMan();   //死亡者を出力
    OutputLastWords(); //遺言を出力
  }
}

//シーン切り替え時のログ出力
function OutputSceneChange($set_date){
  global $reverse_log, $heaven_only, $date, $day_night;

  echo '</table>'."\n";
  if($heaven_only == 'on') return;
  $date = $set_date;
  if($reverse_log == 'on'){
    OutputVoteList(); //投票結果出力
    $day_night = 'night';
    OutputDeadMan();  //死亡者を出力
  }
  else{
    OutputDeadMan();  //死亡者を出力
    OutputVoteList(); //投票結果出力
  }
}
?>
