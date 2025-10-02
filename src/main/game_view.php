<?php
require_once('include/init.php');
$INIT_CONF->LoadFile('user_class', 'talk_class');
$INIT_CONF->LoadClass('ROLES', 'ICON_CONF');

//-- データ収集 --//
$INIT_CONF->LoadRequest('RequestBaseGame'); //引数を取得
$url = 'game_view.php?room_no=' . $RQ_ARGS->room_no;

$DB_CONF->Connect(); // DB 接続

$ROOM = new Room($RQ_ARGS); //村情報をロード
$ROOM->view_mode = true;
$ROOM->system_time = TZTime(); //現在時刻を取得
switch($ROOM->day_night){
case 'day': //昼
  $time_message = '　日没まで ';
  break;

case 'night': //夜
  $time_message = '　夜明けまで ';
  break;
}

//シーンに応じた追加クラスをロード
if($ROOM->IsFinished()){
  $INIT_CONF->LoadClass('VICT_MESS');
}
else{
  $INIT_CONF->LoadClass('ROOM_CONF', 'CAST_CONF', 'ROOM_IMG', 'GAME_OPT_MESS');
}

$USERS = new UserDataSet($RQ_ARGS); //ユーザ情報をロード
$SELF  = new User();
if($ROOM->IsBeforeGame()) $ROOM->LoadVote();

//-- データ出力 --//
OutputHTMLHeader($SERVER_CONF->title . '[観戦]', 'game_view'); //HTMLヘッダ

if($GAME_CONF->auto_reload && $RQ_ARGS->auto_reload != 0){ //自動更新
  echo '<meta http-equiv="Refresh" content="' . $RQ_ARGS->auto_reload . '">'."\n";
}

//シーンに合わせた文字色と背景色 CSS をロード
echo '<link rel="stylesheet" href="css/game_' . $ROOM->day_night . '.css">'."\n";

if($ROOM->IsPlaying()){ //経過時間を取得
  if($ROOM->IsRealTime()){ //リアルタイム制
    list($start_time, $end_time) = GetRealPassTime($left_time, true);
    $on_load = ' onLoad="output_realtime();"';
    OutputRealTimer($start_time, $end_time);
  }
  else{ //会話で時間経過制
    $INIT_CONF->LoadClass('TIME_CONF');
    $left_talk_time = GetTalkPassTime($left_time);
  }
}

echo <<<EOF
</head>
<body{$on_load}>
<a id="game_top"></a>
<table class="login"><tr>
{$ROOM->GenerateTitleTag()}
<td class="login-link">

EOF;

if($GAME_CONF->auto_reload){ //自動更新設定が有効ならリンクを表示
  echo '<a href="' . $url . '&auto_reload=' . $RQ_ARGS->auto_reload . '">[更新]</a>'."\n";
  OutputAutoReloadLink('<a href="' . $url . '&auto_reload=');
}
else{
  echo '<a href="' . $url . '">[更新]</a>'."\n";
}

echo '<a href="./">[戻る]</a>';
if($ROOM->IsFinished()) OutputLogLink();

echo <<<EOF
</td></tr>
<tr><td><form method="POST" action="login.php?room_no={$ROOM->id}">
<label>ユーザ名</label><input type="text" name="uname" size="20">
<label>パスワード</label><input type="password" class="login-password" name="password" size="20">
<input type="hidden" name="login_manually" value="on">
<input type="submit" value="ログイン">
</form></td>

EOF;

if($ROOM->IsBeforeGame()){ //ゲーム開始前なら登録画面のリンクを表示
  echo '<td class="login-link">';
  echo '<a href="user_manager.php?room_no=' . $ROOM->id . '"><span>[住民登録]</span></a>';
  echo '</td>'."\n";
}
echo '</tr></table>'."\n";


if(! $ROOM->IsFinished()){
  OutputGameOption(); //ゲームオプションを表示
}

echo '<table class="time-table"><tr>'."\n";
OutputTimeTable(); //経過日数と生存人数

if($ROOM->IsPlaying()){
  if($ROOM->IsRealTime()){ //リアルタイム制
    echo '<td class="real-time"><form name="realtime_form">'."\n";
    echo '<input type="text" name="output_realtime" size="50" readonly>'."\n";
    echo '</form></td>'."\n";
  }
  elseif($left_talk_time){ //会話で時間経過制
    echo '<td>' . $time_message . $left_talk_time . '</td>'."\n";
  }

  if($left_time == 0){
    echo '</tr><tr>'."\n" . '<td class="system-vote" colspan="2">' . $time_message .
      $MESSAGE->vote_announce . '</td>'."\n";
  }
}
echo '</tr></table>'."\n";

OutputPlayerList(); //プレイヤーリスト
if($ROOM->IsFinished()) OutputVictory(); //勝敗結果
OutputRevoteList(); //再投票メッセージ
OutputTalkLog();    //会話ログ
OutputLastWords();  //遺言
OutputDeadMan();    //死亡者
OutputVoteList();   //投票結果
OutputHTMLFooter(); //HTMLフッタ
