<?php
require_once(dirname(__FILE__) . '/include/game_functions.php');

//引数を取得
$room_no     = (int)$_GET['room_no']; //部屋 No
$auto_reload = (int)$_GET['auto_reload']; //オートリロードの間隔
if($auto_reload != 0 && $auto_reload < $GAME_CONF->auto_reload_list[0])
  $auto_reload = $GAME_CONF->auto_reload_list[0];

$view_mode = 'on';
$url = 'game_view.php?room_no=' . $room_no . '&view_mode=on';

$dbHandle = ConnectDatabase(); // DB 接続

//日付とシーンを取得
$sql = mysql_query("SELECT date, day_night, room_name, room_comment, game_option
			FROM room WHERE room_no = $room_no");
$array = mysql_fetch_assoc($sql);
$date         = $array['date'];
$day_night    = $array['day_night'];
$room_name    = $array['room_name'];
$room_comment = $array['room_comment'];
$game_option  = $array['game_option'];
$real_time    = (strpos($game_option, 'real_time') !== false);
$system_time  = TZTime(); //現在時刻を取得
switch($day_night){
  case 'day': //昼
    $time_message = '　日没まで ';
    break;

  case 'night': //夜
    $time_message = '　夜明けまで ';
    break;
}

OutputHTMLHeader('汝は人狼なりや？[観戦]', 'game_view'); //HTMLヘッダ

if($GAME_CONF->auto_reload && $auto_reload != 0) //自動更新
  echo '<meta http-equiv="Refresh" content="' . $auto_reload . '">'."\n";

//シーンに合わせた文字色と背景色 CSS をロード
echo '<link rel="stylesheet" href="css/game_' . $day_night . '.css">'."\n";

//経過時間を取得
if($real_time){ //リアルタイム制
  list($start_time, $end_time) = GetRealPassTime($left_time, true);
  if($day_night == 'day' || $day_night == 'night'){
    $on_load = ' onLoad="output_realtime();"';
    OutputRealTimer($start_time, $end_time);
  }
}
else{ //会話で時間経過制
  $left_talk_time = GetTalkPassTime($left_time);
}

echo <<<EOF
</head>
<body{$on_load}>
<table class="login" id="game_top"><tr>
<td classs="room"><span>{$room_name}村</span>　〜{$room_comment}〜[{$room_no}番地]</td>
<td class="login-link">

EOF;

if($GAME_CONF->auto_reload){ //自動更新設定が有効ならリンクを表示
  echo '<a href="' . $url . '&auto_reload=' . $auto_reload . '">[更新]</a>'."\n";
  OutputAutoReloadLink('<a href="' . $url . '&auto_reload=');
}
else{
  echo '<a href="' . $url . '">[更新]</a>'."\n";
}

echo <<<EOF
<a href="index.php">[戻る]</a>
</td></tr>
<tr><td><form method="POST" action="login.php?room_no=$room_no">
<label>ユーザ名</label><input type="text" name="uname" size="20">
<label>パスワード</label><input type="password" class="login-password" name="password" size="20">
<input type="hidden" name="login_type" value="manually">
<input type="submit" value="ログイン">
</form></td>

EOF;

if($day_night == 'beforegame'){
  echo '<td class="login-link">';
  echo '<a href="user_manager.php?room_no=' . $room_no . '"><span>[住民登録]</span></a>';
  echo '</td>'."\n";
}
echo '</tr></table>'."\n";

echo '<table class="time-table"><tr>'."\n";
OutputTimeTable(); //経過日数と生存人数

if($day_night == 'day' || $day_night == 'night'){
  if($real_time){ //リアルタイム制
    echo '<td class="real-time"><form name="realtime_form">'."\n";
    echo '<input type="text" name="output_realtime" size="50" readonly>'."\n";
    echo '</form></td>'."\n";
  }
  elseif($left_time){ //発言による仮想時間
    echo '<td>' . $time_message . $left_talk_time . '</td>'."\n";
  }

  if($left_time == 0){
    echo '</tr><tr>'."\n" . '<td class="system-vote" colspan="2">' . $time_message .
      $MESSAGE->vote_announce . '</td>'."\n";
  }
}
echo '</tr></table>'."\n";

OutputPlayerList(); //プレイヤーリスト
if($day_night == 'aftergame') OutputVictory(); //勝敗結果
OutputReVoteList(); //再投票メッセージ
OutputTalkLog();    //会話ログ
OutputLastWords();  //遺言
OutputDeadMan();    //死亡者
OutputVoteList();   //投票結果
OutputHTMLFooter(); //HTMLフッタ

DisconnectDatabase($dbHandle); //DB 接続解除
?>
