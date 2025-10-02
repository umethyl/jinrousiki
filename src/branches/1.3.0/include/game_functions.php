<?php
require_once(dirname(__FILE__) . '/functions.php');

//セッション認証 返り値 OK:ユーザ名 / NG: false
function CheckSession($session_id, $exit = true){
  global $room_no;
  // $ip_address = $_SERVER['REMOTE_ADDR']; //IPアドレス認証は現在は行っていない

  //セッション ID による認証
  $sql = mysql_query("SELECT uname FROM user_entry WHERE room_no = $room_no
			AND session_id ='$session_id' AND user_no > 0");
  $array = mysql_fetch_assoc($sql);
  if(mysql_num_rows($sql) == 1) return $array['uname'];

  if($exit){ //エラー処理
    OutputActionResult('セッション認証エラー',
		       'セッション認証エラー<br>'."\n" .
		       '<a href="index.php" target="_top">トップページ</a>から' .
		       'ログインしなおしてください');
  }
  return false;
}

//HTMLヘッダー出力
function OutputGamePageHeader(){
  global $GAME_CONF, $room_no, $game_option, $view_mode, $log_mode, $dead_mode,
    $heaven_mode, $date, $day_night, $live, $auto_reload, $play_sound, $list_down;

  //リロード間隔の調整
  if($auto_reload != 0 && $auto_reload < $GAME_CONF->auto_reload_list[0])
    $auto_reload = $GAME_CONF->auto_reload_list[0];

  //引数を格納
  $url_header = 'game_frame.php?room_no=' . $room_no . '&auto_reload=' . $auto_reload;
  // if($day_night  != '') $url_header .= '&day_night='  . $day_night; //現在は不要のはず
  if($play_sound != '') $url_header .= '&play_sound=' . $play_sound;
  if($list_down  != '') $url_header .= '&list_down='  . $list_down;

  $title = '汝は人狼なりか？[プレイ]';
  $body  = '<br>'."\n";

  /*
    Mac で JavaScript でエラーを吐くブラウザがあった当時のコード
    現在の Safari、Firefox では不要なので false でスキップしておく
    //if(preg_match('/Mac( OS|intosh|_PowerPC)/i', $_SERVER['HTTP_USER_AGENT'])){
  */
  if(false){
    $sentence = '';
    $anchor_header .= '<a href="';
    $anchor_footer = '" target="_top">ここをクリックしてください</a>';
  }
  else{
    $location = '<script type="text/javascript"><!--'."\n" .
      'if(top != self){ top.location.href = self.location.href; }'."\n" .
      '--></script>'."\n";
    $body .= '切り替わらないなら <a href="';
    $body_footer = '"  target="_top">ここ</a>';
  }

  //ゲーム中、死んで霊話モードに行くとき
  if($day_night != 'aftergame' && $live == 'dead' && $view_mode != 'on' &&
     $log_mode != 'on' && $dead_mode != 'on' && $heaven_mode != 'on'){
    $url =  $url_header . '&dead_mode=on';
    OutputActionResult($title, $location . '天国モードに切り替えます。' .
		       $body . $url . $body_footer, $url);
  }
  elseif($day_night == 'aftergame' && $dead_mode == 'on'){ //ゲームが終了して霊話から戻るとき
    $url = $url_header;
    OutputActionResult($title, $location . 'ゲーム終了後のお部屋に飛びます。' .
		       $body . $url . $body_footer, $url);
  }
  elseif($live == 'live' && ($dead_mode == 'on' || $heaven_mode == 'on')){
    $url = $url_header;
    OutputActionResult($title, $location . 'ゲーム画面に飛びます。' .
		       $body . $url . $body_footer, $url);
  }

  OutputHTMLHeader($title, 'game');
  echo '<link rel="stylesheet" href="css/game_' . $day_night . '.css">'."\n";
  if($log_mode != 'on'){ //過去ログ閲覧時は不要
    echo '<script type="text/javascript" src="javascript/change_css.js"></script>'."\n";
    $on_load  = "change_css('$day_night');";
  }

  //自動リロードをセット
  if($auto_reload != 0 && $day_night != 'aftergame')
    echo '<meta http-equiv="Refresh" content="' . $auto_reload . '">'."\n";

  //ゲーム中、リアルタイム制なら経過時間を Javascript でリアルタイム表示
  if(($day_night == 'day' || $day_night == 'night') && strpos($game_option, 'real_time') !== false &&
     $heaven_mode != 'on' && $log_mode != 'on'){
    list($start_time, $end_time) = GetRealPassTime($left_time, true);
    $on_load .= 'output_realtime();';
    OutputRealTimer($start_time, $end_time);
  }
  echo '</head>'."\n";
  echo '<body onLoad="' . $on_load . '">'."\n";
  echo '<a id="game_top"></a>'."\n";
}

//リアルタイム表示に使う JavaScript の変数を出力
function OutputRealTimer($start_time, $end_time){
  global $day_night;

  echo '<script type="text/javascript" src="javascript/output_realtime.js"></script>'."\n";
  echo '<script language="JavaScript"><!--'."\n";
  echo 'var realtime_message = "　' . ($day_night == 'day' ? '日没' : '夜明け') . 'まで ";'."\n";
  echo 'var start_time = "' . $start_time . '";'."\n";
  echo 'var end_time = "'   . $end_time   . '";'."\n";
  echo '// --></script>'."\n";
}

//自動更新のリンクを出力
function OutputAutoReloadLink($url){
  global $GAME_CONF, $auto_reload;

  echo '[自動更新](' . ($auto_reload == 0 ? $url . '0">【手動】</a>' : $url . '0">手動</a>');
  foreach($GAME_CONF->auto_reload_list as $time){
    $name = $time . '秒';
    echo  ' ' . ($auto_reload == $time ? $url . $time . '">【' . $name . '】</a>' : $url . $time . '">' . $name . '</a>');
  }
  echo ')'."\n";
}

//日付と生存者の人数を出力
function OutputTimeTable(){
  global $room_no, $date;

  //出力条件をチェック
  if($date < 1) return false;

  //生存者の数を取得
  $sql = mysql_query("SELECT COUNT(uname) FROM user_entry WHERE room_no = $room_no
			AND live = 'live' AND user_no > 0");
  $count = mysql_result($sql, 0, 0);
  echo '<td>' . $date . ' 日目<span>(生存者' . $count . '人)</span></td>'."\n";
}

//プレイヤー一覧出力
function OutputPlayerList(){
  global $DEBUG_MODE, $ICON_CONF, $room_no, $game_option, $day_night, $live;

  $sql = mysql_query("SELECT user_entry.uname,
			user_entry.handle_name,
			user_entry.profile,
			user_entry.live,
			user_entry.role,
			user_entry.user_no,
			user_icon.icon_filename,
			user_icon.color
			FROM user_entry, user_icon
			WHERE user_entry.room_no = $room_no
			AND user_entry.icon_no = user_icon.icon_no
			AND user_entry.user_no > 0
			ORDER BY user_entry.user_no");
  $count  = mysql_num_rows($sql);
  $width  = $ICON_CONF->width;
  $height = $ICON_CONF->height;
  //ブラウザをチェック (MSIE @ Windows だけ 画像の Alt, Title 属性で改行できる)
  //IE の場合改行を \r\n に統一、その他のブラウザはスペースにする(画像のAlt属性)
  $replace = (preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT']) ? "\r\n" : ' ');

  echo '<div class="player"><table cellspacing="5"><tr>'."\n";
  for($i=0; $i < $count; $i++){
    //5個ごとに段落改行
    if($i > 0 && ($i % 5) == 0) echo '</tr>'."\n".'<tr>'."\n";

    $array = mysql_fetch_assoc($sql);
    $this_uname   = $array['uname'];
    $this_handle  = $array['handle_name'];
    $this_profile = $array['profile'];
    $this_live    = $array['live'];
    $this_role    = $array['role'];
    $this_user_no = $array['user_no'];
    $this_file    = $array['icon_filename'];
    $this_color   = $array['color'];
    $profile_alt  = str_replace("\n", $replace, $this_profile);
    if($DEBUG_MODE) $this_handle .= ' (' . $this_user_no . ')';

    //アイコン
    $path = $ICON_CONF->path . '/' . $this_file;
    $img_tag = '<img title="' . $profile_alt . '" alt="' . $profile_alt . '"' .
      ' style="border-color: ' . $this_color . ';"';
    if($this_live == 'live'){ //生きていればユーザアイコン
      // $img_tag .= ' width="' . $this_width . '" height="' . $this_height . '"'; //サイズは固定
      $this_live_str   = '(生存中)';
    }
    else{ //死んでれば死亡アイコン
      $this_live_path = $path; //アイコンのパスを入れ替え
      $path           = $ICON_CONF->dead;
      $this_live_str  = '(死亡)';
      $img_tag .= " onMouseover=\"this.src='$this_live_path'\" onMouseout=\"this.src='$path'\"";
    }
    $img_tag .= ' width="' . $width . '" height="' . $height . '"';
    $img_tag .= ' src="' . $path . '">';

    //ゲーム終了後か死亡後かつ霊界役職公開の場合は、役職・ユーザネームも表示
    if($day_night == 'aftergame' ||
       ($live == 'dead' && strpos($game_option, 'not_open_cast') === false)){
      $role_str = '';
      if(strpos($this_role, 'human') !== false)
	$role_str = '<span class="human">[村人]</span>';
      elseif(strpos($this_role, 'wolf') !== false)
        $role_str = '<span class="wolf">[人狼]</span>';
      elseif(strpos($this_role, 'mage') !== false)
        $role_str = '<span class="mage">[占い師]</span>';
      elseif(strpos($this_role, 'necromancer') !== false)
	$role_str = '<span class="necromancer">[霊能者]</span>';
      elseif(strpos($this_role, 'mad') !== false)
	$role_str = '<span class="mad">[狂人]</span>';
      elseif(strpos($this_role, 'common') !== false)
	$role_str = '<span class="common">[共有者]</span>';
      elseif(strpos($this_role, 'guard') !== false)
	$role_str = '<span class="guard">[狩人]</span>';
      elseif(strpos($this_role, 'fox') !== false)
	$role_str = '<span class="fox">[妖狐]</span>';
      elseif(strpos($this_role, 'poison') !== false)
	$role_str = '<span class="poison">[埋毒者]</span>';
      elseif(strpos($this_role, 'cupid') !== false)
	$role_str = '<span class="cupid">[キューピッド]</span>';

      if(strpos($this_role, 'authority') !== false) $role_str .= '<br><span class="authority">[権力者]</span>';
      if(strpos($this_role, 'decide') !== false)    $role_str .= '<br><span class="decide">[決定者]</span>';
      if(strpos($this_role, 'lovers') !== false)    $role_str .= '<br><span class="lovers">[恋人]</span>';

      echo "<td>${img_tag}</td>"."\n";
      echo "<td><font color=\"$this_color\">◆</font>$this_handle<br>"."\n";
      echo "　($this_uname)<br> $role_str";
    }
    elseif($day_night == 'beforegame'){ //ゲーム前
      //ゲームスタートに投票しているか、していれば色を変える
      $sql_start = mysql_query("select count(uname) from vote where room_no = $room_no
				and situation = 'GAMESTART' and uname = '$this_uname'");

      if((mysql_result($sql_start, 0, 0) == 1) || $this_uname == "dummy_boy")
	$already_vote_class = ' class="already-vote"';
      else
	$already_vote_class = '';

      echo "<td${already_vote_class}>{$img_tag}</td>"."\n";
      echo "<td${already_vote_class}><font color=\"$this_color\">◆</font>$this_handle";
    }
    else{ //生きていてゲーム中
      echo "<td>{$img_tag}</td>"."\n";
      echo "<td><font color=\"$this_color\">◆</font>$this_handle";
    }
    echo '<br>'."\n" . $this_live_str . '</td>'."\n";
  }
  echo '</tr></table></div>'."\n";
}

//勝敗の出力
function OutputVictory(){
  global $MESSAGE, $room_no, $view_mode, $log_mode, $role;

  $sql = mysql_query("SELECT victory_role FROM room WHERE room_no = $room_no");
  $victory = mysql_result($sql, 0, 0);
  $class   = $victory;
  $winner  = 'victory_' . $victory;

  switch($victory){
    case 'fox1': //狐
    case 'fox2':
      $class = 'fox';
      break;

    case 'draw':   //引き分け
    case 'vanish': //全滅
      $class = 'draw';
      break;

    case NULL: //廃村
      $class  = 'none';
      $winner = 'victory_none';
      break;
  }
  echo <<<EOF
<table class="victory victory-{$class}">
<tr><td>{$MESSAGE->$winner}</td></tr>
</table>

EOF;

  //個々の結果を出力
  //勝敗未決定、観戦モード、ログ閲覧モードなら非表示
  if($victory == NULL || $view_mode == 'on' || $log_mode == 'on') return;

  $result = 'win';
  $lovers = (strpos($role, 'lovers') !== false || strpos($role, 'cupid') !== false);
  if($victory == 'human' && ! $lovers &&
     (strpos($role, 'human') !== false || strpos($role, 'mage') !== false   || strpos($role, 'necromancer') !== false ||
      strpos($role, 'guard') !== false || strpos($role, 'common') !== false || strpos($role, 'poison') !== false)){
    $class = 'human';
  }
  elseif($victory == 'wolf' && (strpos($role, 'wolf') !== false || strpos($role, 'mad') !== false) && ! $lovers){
    $class = 'wolf';
  }
  elseif(strpos($victory, 'fox') !== false && strpos($role, 'fox') !== false && ! $lovers){
    $class = 'fox';
  }
  elseif($victory == 'lovers' && $lovers){
    $class = 'lovers';
  }
  elseif($victory == 'draw' || $victory == 'vanish'){
    $class  = 'draw';
    $result = 'draw';
  }
  else{
    $class  = 'lose';
    $result = 'lose';
  }

  echo <<<EOF
<table class="victory victory-{$class}">
<tr><td>{$MESSAGE->$result}</td></tr>
</table>

EOF;
}

//再投票の時、メッセージを表示
function OutputReVoteList(){
  global $GAME_CONF, $MESSAGE, $SOUND, $room_no, $view_mode, $date, $day_night,
    $uname, $play_sound, $cookie_vote_times;

  //出力条件をチェック
  if($day_night != 'day') return false;

  //再投票の回数を取得
  $sql = mysql_query("SELECT message FROM system_message WHERE room_no = $room_no
			AND date = $date AND type = 'RE_VOTE' ORDER BY message DESC");
  if(mysql_num_rows($sql) == 0) return false;

  //何回目の再投票なのか取得
  $last_vote_times = (int)mysql_result($sql, 0, 0);

  //音を鳴らす
  if($play_sound == 'on' && $view_mode != 'on' && $last_vote_times > $cookie_vote_times)
    OutputSound($SOUND->revote);

  $this_vote_times = $last_vote_times + 1;
  $sql = mysql_query("SELECT COUNT(uname) FROM vote WHERE room_no = $room_no AND date = $date
			AND vote_times = $this_vote_times AND uname = '$uname'");
  $already_vote = mysql_result($sql, 0, 0);

  if($already_vote == 0){
    echo '<div class="revote">' . $MESSAGE->revote . ' (' . $GAME_CONF->draw . '回' .
      $MESSAGE->draw_announce . ')</div><br>';
  }
  OutputVoteListDay($date);
}

//音を鳴らす
function OutputSound($sound, $loop = false){
  if($loop) $loop_tag = "\n".'<param name="loop" value="true">';

echo <<< EOF
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=4,0,0,0" width="0" height="0">
<param name="movie" value="{$sound}">
<param name="quality" value="high">{$loop_tag}
<embed src="{$sound}" type="application/x-shockwave-flash" quality="high" width="0" height="0" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash">
</embed>
</object>

EOF;
}

//会話ログ出力
function OutputTalkLog(){
  global $MESSAGE, $room_no, $game_option, $status, $date, $day_night, $uname, $role, $live;

  //会話のユーザ名、ハンドル名、発言、発言のタイプを取得
  $sql = mysql_query("SELECT user_entry.uname AS talk_uname,
			user_entry.handle_name AS talk_handle_name,
			user_entry.sex AS talk_sex,
			user_icon.color AS talk_color,
			talk.sentence AS sentence,
			talk.font_type AS font_type,
			talk.location AS location
			FROM user_entry,talk,user_icon
			WHERE talk.room_no = $room_no
			AND talk.location LIKE '$day_night%'
			AND talk.date = $date
			AND ((user_entry.room_no = $room_no AND user_entry.uname = talk.uname
			AND user_entry.icon_no = user_icon.icon_no)
			OR (user_entry.room_no = 0 AND talk.uname = 'system'
			AND user_entry.icon_no = user_icon.icon_no))
			ORDER BY time DESC");
  $count = mysql_num_rows($sql);

  echo '<table class="talk">'."\n";
  for($i=0; $i < $count; $i++) OutputTalk(mysql_fetch_assoc($sql)); //会話出力
  echo '</table>'."\n";
}

//会話出力
function OutputTalk($array){
  global $GAME_CONF, $MESSAGE, $game_option, $status, $day_night, $uname, $live, $role;

  $talk_uname       = $array['talk_uname'];
  $talk_handle_name = $array['talk_handle_name'];
  $talk_sex         = $array['talk_sex'];
  $talk_color       = $array['talk_color'];
  $sentence         = $array['sentence'];
  $font_type        = $array['font_type'];
  $location         = $array['location'];

  LineToBR($sentence); //改行コードを <br> に変換
  $location_system     = (strpos($location, 'system') !== false);
  $flag_vote           = (strpos($sentence, 'VOTE_DO')           === 0);
  $flag_wolf           = (strpos($sentence, 'WOLF_EAT')          === 0);
  $flag_mage           = (strpos($sentence, 'MAGE_DO')           === 0);
  $flag_guard          = (strpos($sentence, 'GUARD_DO')          === 0);
  $flag_cupid          = (strpos($sentence, 'CUPID_DO')          === 0);
  $flag_system = ($location_system &&
		  ($flag_vote  || $flag_wolf || $flag_mage || $flag_guard || $flag_cupid));

  if($location_system && $sentence == 'OBJECTION'){ //異議あり
    echo '<tr class="system-message">'."\n";
    echo '<td class="objection-' . $talk_sex . '" colspan="2">' .
      $talk_handle_name . ' ' . $MESSAGE->objection . '</td>'."\n";
    echo '</tr>'."\n";
  }
  elseif($location_system && $sentence == 'GAMESTART_DO'){ //ゲーム開始投票
    /*
      echo '<tr class="system-message">'."\n";
      echo '<td class="game-start" colspan="2">' . $talk_handle_name . ' ' .
      $MESSAGE->game_start . '</td>'."\n";
      echo '</tr>'."\n";
    */
  }
  elseif($location_system && strpos($sentence, 'KICK_DO') === 0){ //KICK 投票
    $target_handle_name = ParseStrings($sentence, 'KICK_DO');
    echo '<tr class="system-message">'."\n";
    echo '<td class="kick" colspan="2">' . $talk_handle_name . ' は ' .
      $target_handle_name . ' ' . $MESSAGE->kick_do . '</td>'."\n";
    echo '</tr>'."\n";
    }
  elseif($live == 'live' && $flag_system){ //生存中は投票情報は非表示
  }
  elseif($talk_uname == 'system'){ //システムメッセージ
    echo '<tr>'."\n";
    if(strpos($sentence, 'MORNING') === 0){
      sscanf($sentence, "MORNING\t%d", &$morning_date);
      echo '<td class="system-user" width="1000" colspan="2">&lt; &lt; ' .
	$MESSAGE->morning_header . ' ' . $morning_date . $MESSAGE->morning_footer .
	' &gt; &gt;</td>'."\n";
    }
    elseif(strpos($sentence, 'NIGHT') === 0){
      echo '<td class="system-user" colspan="2">' .
	'&lt; &lt; ' . $MESSAGE->night . ' &gt; &gt;</td>'."\n";
    }
    else{
      echo '<td class="system-user" colspan="2">' . $sentence . '</td>'."\n";
    }
    echo '</tr>'."\n";
  }
  //身代わり君はゲーム中はシステムメッセージ相当
  elseif($talk_uname == 'dummy_boy' && ($day_night == 'day' || $day_night == 'night')){
    echo '<tr>'."\n";
    echo '<td class="system-user" colspan="2">' . $MESSAGE->dummy_boy . $sentence . '</td>'."\n";
    echo '</tr>'."\n";
  }
  //開始前と終了後 || ゲーム中、生きている人の昼
  elseif($day_night == 'beforegame' || $day_night == 'aftergame' ||
	 ($live == 'live' && $day_night == 'day' && $location == 'day')){
    if($GAME_CONF->quote_words) $sentence = '「' . $sentence . '」';
    echo '<tr class="user-talk">'."\n";
    echo '<td class="user-name"><font color="' . $talk_color . '">◆</font>' .
      $talk_handle_name . '</td>'."\n";
    echo '<td class="say ' . $font_type . '">' . $sentence . '</td>'."\n";
    echo '</tr>'."\n";
  }
  //ゲーム中、生きている人の夜の狼
  elseif($live == 'live' && $day_night == 'night' && $location == 'night wolf'){
    if($GAME_CONF->quote_words) $sentence = '「' . $sentence . '」';
    if(strpos($role, 'wolf') !== false){
      $talk_handle_name = '<font color="' . $talk_color . '">◆</font>' . $talk_handle_name;
    }
    else{
      $talk_handle_name = '狼の遠吠え';
      $sentence = $MESSAGE->wolf_howl;
    }
    echo '<tr class="user-talk">'."\n";
    echo '<td class="user-name">' . $talk_handle_name . '</td>'."\n";
    echo '<td class="say ' . $font_type . '">' . $sentence . '</td>'."\n";
    echo '</tr>'."\n";
  }
  //ゲーム中、生きている人の夜の共有者
  elseif($live == 'live' && $day_night == 'night' && $location == 'night common'){
    if($GAME_CONF->quote_words) $sentence = '「' . $sentence . '」';
    echo '<tr class="user-talk">'."\n";
    if(strpos($role, 'common') !== false){
      echo '<td class="user-name"><font color="' . $talk_color . '">◆</font>' .
	$talk_handle_name . '</td>'."\n";
      echo '<td class="say ' . $font_type . '">' . $sentence . '</td>'."\n";
    }
    else{
      echo '<td class="user-name talk-common">共有者の小声</td>'."\n";
      echo '<td class="say say-common">' . $MESSAGE->common_talk . '</td>'."\n";
    }
    echo '</tr>'."\n";
  }
  //ゲーム中、生きている人の夜の妖狐
  elseif($live == 'live' && $day_night == 'night' && $location == 'night fox'){
    if(strpos($role, 'fox') !== false){
      if($GAME_CONF->quote_words) $sentence = '「' . $sentence . '」';
      echo '<tr class="user-talk">'."\n";
      echo '<td class="user-name"><font color="' . $talk_color . '">◆</font>' .
	$talk_handle_name . '</td>'."\n";
      echo '<td class="say ' . $font_type . '">' . $sentence . '</td>'."\n";
      echo '</tr>'."\n";
    }
  }
  //ゲーム中、生きている人の夜の独り言
  elseif($live == 'live' && $day_night == 'night' && $location == 'night self_talk'){
    if($uname == $talk_uname){
      if($GAME_CONF->quote_words) $sentence = '「' . $sentence . '」';
      echo '<tr class="user-talk">'."\n";
      echo '<td class="user-name"><font color="' . $talk_color . '">◆</font>' .
	$talk_handle_name . '<span>の独り言</span></td>'."\n";
      echo '<td class="say ' . $font_type . '">' . $sentence . '</td>'."\n";
      echo '</tr>'."\n";
    }
  }
  //ゲーム終了 / 身代わり君(仮想GM用) / ゲーム中、死亡者(非公開オプション時は不可)
  elseif($status == 'finished' || $uname == 'dummy_boy' ||
	 ($live == 'dead' && strpos($game_option, 'not_open_cast') === false)){
    if($location_system && $flag_vote){ //処刑投票
      $target_handle_name = ParseStrings($sentence, 'VOTE_DO');
      echo '<tr class="system-message">'."\n";
      echo '<td class="vote" colspan="2">' . $talk_handle_name . ' は ' .
	$target_handle_name . ' ' . $MESSAGE->vote_do . '</td>'."\n";
    }
    elseif($location_system && $flag_wolf){ //狼の投票
      $target_handle_name = ParseStrings($sentence, 'WOLF_EAT');
      echo '<tr class="system-message">'."\n";
      echo '<td class="wolf-eat" colspan="2">' . $talk_handle_name . ' たち人狼は ' .
	$target_handle_name . ' ' . $MESSAGE->wolf_eat . '</td>'."\n";
    }
    elseif($location_system && $flag_mage){ //占い師の投票
      $target_handle_name = ParseStrings($sentence, 'MAGE_DO');
      echo '<tr class="system-message">'."\n";
      echo '<td class="mage-do" colspan="2">' . $talk_handle_name . ' は ' .
	$target_handle_name . ' ' . $MESSAGE->mage_do . '</td>'."\n";
    }
    elseif($location_system && $flag_guard){ //狩人の投票
      $target_handle_name = ParseStrings($sentence, 'GUARD_DO');
      echo '<tr class="system-message">'."\n";
      echo '<td class="guard-do" colspan="2">' . $talk_handle_name . ' は ' .
	$target_handle_name . ' ' . $MESSAGE->guard_do . '</td>'."\n";
    }
    elseif($location_system && $flag_cupid){ //キューピッドの投票
      $target_handle_name = ParseStrings($sentence, 'CUPID_DO');
      echo '<tr class="system-message">'."\n";
      echo '<td class="cupid-do" colspan="2">' . $talk_handle_name . ' は ' .
	$target_handle_name . ' ' . $MESSAGE->cupid_do . '</td>'."\n";
    }
    else{ //その他の全てを表示(死者の場合)
      if($GAME_CONF->quote_words) $sentence = '「' . $sentence . '」';
      $base_class = 'user-talk';
      $talk_class = 'user-name';
      switch($location){
        case 'night self_talk':
	  $talk_handle_name .= '<span>の独り言</span>';
	  $talk_class  .= ' night-self-talk';
	  break;

	case 'night wolf':
	  $talk_handle_name .= '<span>(人狼)</span>';
	  $talk_class  .= ' night-wolf';
	  $font_type   .= ' night-wolf';
	  break;

	case 'night common':
	  $talk_handle_name .= '<span>(共有者)</span>';
	  $talk_class  .= ' night-common';
	  $font_type   .= ' night-common';
	  break;

	case 'night fox':
	  $talk_handle_name .= '<span>(妖狐)</span>';
	  $talk_class  .= ' night-fox';
	  $font_type   .= ' night-fox';
	  break;

	case 'heaven':
	  $base_class  .= ' heaven';
	  break;
      }
      echo '<tr class="' . $base_class . '">'."\n";
      echo '<td class="' . $talk_class . '"><font color="' . $talk_color . '">◆</font>' .
	$talk_handle_name . '</td>'."\n";
      echo '<td class="say ' . $font_type . '">' . $sentence . '</td>'."\n";
    }
    echo '</tr>'."\n";
  }
  //ここからは観戦者と役職非公開モード
  elseif($flag_system){ //投票情報は非表示
  }
  else{ //観戦者
    if($GAME_CONF->quote_words) $sentence = '「' . $sentence . '」';
    if($day_night == 'night' && $location == 'night wolf'){
      if(strpos($role, 'wolf') !== false){
	$talk_handle_name = '<font color="' . $talk_color . '">◆</font>' . $talk_handle_name;
      }
      else{
	$talk_handle_name = '狼の遠吠え';
	$sentence = $MESSAGE->wolf_howl;
      }
      echo '<tr class="user-talk">'."\n";
      echo '<td class="user-name">' . $talk_handle_name . '</td>'."\n";
      echo '<td class="say ' . $font_type . '">' . $sentence . '</td>'."\n";
      echo '</tr>'."\n";
    }
    elseif($day_night == 'night' && $location == 'night common'){
      echo '<tr class="user-talk">'."\n";
      if(strpos($role, 'common') !== false){
	echo '<td class="user-name"><font color="' . $talk_color . '">◆</font>' .
	  $talk_handle_name . '</td>'."\n";
	echo '<td class="say ' . $font_type . '">' . $sentence . '</td>'."\n";
      }
      else{
	echo '<td class="user-name talk-common">共有者の小声</td>'."\n";
	echo '<td class="say say-common">' . $MESSAGE->common_talk . '</td>'."\n";
      }
      echo '</tr>'."\n";
    }
    elseif($day_night == 'night' && $location == 'night fox'){
      if(strpos($role, 'fox') !== false){
	echo '<tr class="user-talk">'."\n";
	echo '<td class="user-name"><font color="' . $talk_color . '">◆</font>' .
	  $talk_handle_name . '</td>'."\n";
	echo '<td class="say ' . $font_type . '">' . $sentence . '</td>'."\n";
	echo '</tr>'."\n";
      }
      else{
	//狐以外なら表示しない
      }
    }
    elseif(! ($day_night == 'night' && $location == 'night self_talk')){
      echo '<tr class="user-talk">'."\n";
      echo '<td class="user-name"><font color="' . $talk_color . '">◆</font>' .
	$talk_handle_name . '</td>'."\n";
      echo '<td class="say ' . $font_type . '">' . $sentence . '</td>'."\n";
      echo '</tr>'."\n";
    }
  }
}

//死亡者の遺言を出力
function OutputLastWords(){
  global $MESSAGE, $room_no, $date, $day_night;

  //出力条件をチェック
  if($day_night == 'beforegame' || $day_night == 'aftergame') return false;

  //前日の死亡者遺言を出力
  $set_date = $date - 1;
  $sql = mysql_query("SELECT message, MD5(RAND()*NOW()) AS rand FROM system_message
			WHERE room_no = $room_no AND date = $set_date
			AND type = 'LAST_WORDS' ORDER BY rand");
  $count = mysql_num_rows($sql);
  if($count < 1) return false;

  echo <<<EOF
<table class="system-lastwords"><tr>
<td>{$MESSAGE->lastwords}</td>
</tr></table>
<table class="lastwords">

EOF;

  for($i=0; $i < $count; $i++){
    $result = mysql_result($sql, $i, 0);
    LineToBR($result);
    list($handle, $str) = ParseStrings($result);

    echo <<<EOF
<tr>
<td class="lastwords-title">{$handle}<span>さんの遺言</span></td>
<td class="lastwords-body">{$str}</td>
</tr>

EOF;
  }
  echo '</table>'."\n";
}

//前の日の 狼が食べた、狐が占われて死亡、投票結果で死亡のメッセージ
function OutputDeadMan(){
  global $room_no, $date, $day_night, $log_mode;

  if($day_night == 'beforegame' || $day_night == 'aftergame') return false;

  $yesterday = $date - 1;

  //共通クエリ
  $query_header = "SELECT message, type, MD5(RAND()*NOW()) AS MyRand FROM system_message " .
    "WHERE room_no = $room_no AND date =";

  //処刑メッセージ、毒死メッセージ(昼)
  $type_day = "type = 'VOTE_KILLED' OR type = 'POISON_DEAD_day' OR type = 'LOVERS_FOLLOWED_day'";

  //前の日の夜に起こった死亡メッセージ
  $type_night = "type = 'WOLF_KILLED' OR type = 'FOX_DEAD' OR " .
    "type = 'POISON_DEAD_night' OR type = 'LOVERS_FOLLOWED_night'";

  if($day_night == 'day'){
    $set_date = $yesterday;
    $type = $type_night;
  }
  else{
    $set_date = $date;
    $type = $type_day;
  }

  $sql = mysql_query("$query_header $set_date AND ( $type ) ORDER BY MyRand");
  $count = mysql_num_rows($sql); //死亡者の人数
  for($i=0; $i < $count; $i++){
    $array = mysql_fetch_assoc($sql);
    OutputDeadManType($array['message'], $array['type']); //死者のハンドルネームとタイプ
  }

  //ログ閲覧モード以外なら二つ前も死亡者メッセージ表示
  if($log_mode == 'on') return;
  $set_date = $yesterday;
  $type = ($day_night == 'day' ? $type_day : $type_night);

  $sql = mysql_query("$query_header $set_date AND ( $type ) ORDER BY MyRand");
  $count = mysql_num_rows($sql); //死亡者の人数
  for($i=0 ; $i < $count ;$i++){
    $array = mysql_fetch_assoc($sql);
    OutputDeadManType($array['message'], $array['type']);
  }
}

//死者のタイプ別に死亡メッセージを出力
function OutputDeadManType($name, $type){
  global $live, $game_option, $day_night, $status, $MESSAGE;

  $deadman = '<tr><td>' . $name . ' ' . $MESSAGE->deadman . '</td>'; //基本メッセージ
  $reason_header = '</tr>'."\n" . '<tr><td>(' . $name . ' '; //追加共通ヘッダ
  $show_reason = ($status == 'finished' ||
		  ($live == 'dead' && strpos($game_option, 'not_open_cast') === false));

  echo '<table class="dead-type">'."\n";
  switch($type){
    case 'WOLF_KILLED':
      echo $deadman;
      if($show_reason) echo $reason_header . $MESSAGE->wolf_killed . ')</td>';
      break;

    case 'FOX_DEAD':
      echo $deadman;
      if($show_reason) echo $reason_header . $MESSAGE->fox_dead . ')</td>';
      break;

    case 'POISON_DEAD_day':
    case 'POISON_DEAD_night':
      echo $deadman;
      if($show_reason) echo $reason_header . $MESSAGE->poison_dead . ')</td>';
      break;

    case 'VOTE_KILLED':
      echo '<tr class="dead-type-vote">';
      echo '<td>' . $name . ' ' . $MESSAGE->vote_killed . '</td>';
      break;

    case 'LOVERS_FOLLOWED_day':
    case 'LOVERS_FOLLOWED_night':
      echo '<tr><td>' . $name . ' ' . $MESSAGE->lovers_followed . '</td>';
      break;
  }
  echo '</tr>'."\n".'</table>'."\n";
}

//投票の集計出力
function OutputVoteList(){
  global $date, $day_night, $log_mode;

  //出力条件をチェック
  if($day_night == 'beforegame' || $day_night == 'aftergame' ) return false;

  if($day_night == 'day' && $log_mode != 'on') //昼だったら前の日の集計を取得
    OutputVoteListDay($date - 1);
  else //夜だったら今日の集計を取得
    OutputVoteListDay($date);
}

//指定した日付の投票結果を出力する
function OutputVoteListDay($set_date){
  global $room_no, $game_option, $live, $reverse_log, $view_mode;

  //指定された日付の投票結果を取得
  $sql = mysql_query("SELECT message FROM system_message WHERE room_no = $room_no
		      AND date = $set_date and type = 'VOTE_KILL'");
  if(mysql_num_rows($sql) == 0) return false;

  $result_array = array(); //投票結果を格納する
  $this_vote_times = -1; //出力する投票回数を記録
  $this_vote_count = mysql_num_rows($sql); //投票総数
  $table_count = 0; //表の個数

  for($i=0; $i < $this_vote_count; $i++){ //いったん配列に格納する
    $vote_array = mysql_fetch_assoc($sql);
    $vote_message = $vote_array['message'];

    //タブ区切りのデータを分割する
    list($handle_name, $target_name, $voted_number,
	 $vote_number, $vote_times) = ParseStrings($vote_message, 'VOTE');

    if($this_vote_times != $vote_times){ //投票回数が違うデータだと別テーブルにする
      if($this_vote_times != -1)
	array_push($result_array[$this_vote_times], '</table>'."\n");

      $this_vote_times = $vote_times;
      $result_array[$vote_times] = array();
      array_push($result_array[$vote_times], '<table class="vote-list">'."\n");
      array_push($result_array[$vote_times], '<td class="vote-times" colspan="4">' .
		 $set_date . ' 日目 ( ' . $vote_times . ' 回目)</td>'."\n");

      $table_count++;
    }

    if((strpos($game_option, 'open_vote') !== false || $live == 'dead') && $view_mode != 'on')
      $vote_number_str = '投票先 ' . $vote_number . ' 票 →';
    else
      $vote_number_str = '投票先→';

    //表示されるメッセージ
    $this_vote_message = '<tr><td class="vote-name">' . $handle_name . '</td><td>' .
      $voted_number . ' 票</td><td>' . $vote_number_str .
      '</td><td class="vote-name"> ' . $target_name . ' </td></tr>'."\n";

    array_push($result_array[$vote_times], $this_vote_message);
  }
  array_push($result_array[$this_vote_times], '</table>'."\n");

  if($reverse_log == 'on'){ //逆順表示
    //配列に格納されたデータを出力
    for($i=1; $i <= $table_count; $i++){
      $this_vote_count = (int)count($result_array[$i]);
      for($j=0; $j < $this_vote_count; $j++) echo $result_array[$i][$j];
    }
  }
  else{
    //配列に格納されたデータを出力
    for($i=$table_count; $i > 0; $i--){
      $this_vote_count = (int)count($result_array[$i]);
      for($j=0; $j < $this_vote_count; $j++) echo $result_array[$i][$j];
    }
  }
}

//占う、狼が狙う、護衛する等、能力を使うメッセージ
function OutputAbilityAction(){
  global $room_no, $date, $day_night, $game_option;

  //出力条件をチェック
  if(strpos($game_option, 'not_open_cast') !== false || $day_night != 'day') return false;

  $yesterday = $date - 1;
  $result = mysql_query("SELECT message,type FROM system_message WHERE room_no = $room_no
			 AND date = $yesterday AND ( type = 'MAGE_DO' OR type = 'WOLF_EAT'
			 OR type = 'GUARD_DO' OR type = 'CUPID_DO')");
  $count = mysql_num_rows($result);
  $header = '<strong>前日の夜、';
  $footer = 'ました</strong><br>'."\n";

  for($i=0; $i < $count; $i++){
    $array = mysql_fetch_assoc($result);
    $message = $array['message'];
    $type    = $array['type'];

    list($handle_name, $target_name) = ParseStrings($message);
    switch($type){
      case 'MAGE_DO':
	echo $header . '占い師 ' . $handle_name . 'は ' . $target_name . ' を占い' . $footer;
	break;

      case 'WOLF_EAT':
	echo $header . $handle_name . ' ら人狼たちは ' . $target_name . ' を狙い' . $footer;
	break;

      case 'GUARD_DO':
	echo $header . '狩人 ' . $handle_name . ' は ' . $target_name . ' を護衛し' . $footer;
	break;

      case 'CUPID_DO':
	echo $header . 'キューピッド ' . $handle_name . ' は ' . $target_name . 'に愛の矢を放ち' . $footer;
	break;
    }
  }
}

//リアルタイムの経過時間
function GetRealPassTime(&$left_time, $flag = false){
  global $system_time, $room_no, $game_option, $date, $day_night;

  $time_str = strstr($game_option, 'real_time');
  //実時間の制限時間を取得
  sscanf($time_str, 'real_time:%d:%d', &$day_minutes, &$night_minutes);
  $day_time   = $day_minutes   * 60; //秒になおす
  $night_time = $night_minutes * 60; //秒になおす

  //最も小さな時間(場面の最初の時間)を取得
  $sql = mysql_query("SELECT MIN(time) FROM talk WHERE room_no = $room_no
			AND date = $date AND location LIKE '$day_night%'");
  $start_time = (int)mysql_result($sql, 0, 0);

  if($start_time != NULL){
    $pass_time = $system_time - $start_time; //経過した時間
  }
  else{
    $pass_time = 0;
    $start_time = $system_time;
  }
  $base_time = ($day_night == 'day' ? $day_time : $night_time);
  $left_time = $base_time - $pass_time;
  if($left_time < 0) $left_time = 0; //マイナスになったらゼロにする
  if(! $flag) return;

  $start_date_str = gmdate('Y, m, j, G, i, s', $start_time);
  $end_date_str   = gmdate('Y, m, j, G, i, s', $start_time + $base_time);
  return array($start_date_str, $end_date_str);
}

//会話で時間経過制の経過時間
function GetTalkPassTime(&$left_time, $flag = false){
  global $TIME_CONF, $room_no, $date, $day_night;

  $sql = mysql_query("SELECT SUM(spend_time) FROM talk WHERE room_no = $room_no
			AND date = $date AND location LIKE '$day_night%'");
  $spend_time = (int)mysql_result($sql, 0, 0);

  if($day_night == 'day'){ //昼は12時間
    $base_time = $TIME_CONF->day;
    $full_time = 12;
  }
  else{ //夜は6時間
    $base_time = $TIME_CONF->night;
    $full_time = 6;
  }
  $left_time = $base_time - $spend_time;
  if($left_time < 0){ //マイナスになったらゼロにする
    $left_time = 0;
  }

  //仮想時間の計算
  $base_left_time = ($flag ? $TIME_CONF->silence_pass : $left_time);
  return ConvertTime($full_time * $base_left_time * 60 * 60 / $base_time);
}

//勝敗をチェック
function CheckVictory($check_draw = false){
  global $GAME_CONF, $room_no, $date, $day_night, $vote_times;

  $query_end_header = "UPDATE room SET status = 'finished', day_night = 'aftergame'";
  $query_end_footer = "WHERE room_no = $room_no";
  $query_count = "SELECT COUNT(uname) FROM user_entry WHERE room_no = $room_no " .
    "AND live = 'live' AND user_no > 0 AND ";

  //狼の数を取得
  $sql = mysql_query($query_count . "role LIKE 'wolf%'");
  $wolf = (int)mysql_result($sql, 0, 0);

  //狼、狐以外の数を取得
  $sql = mysql_query($query_count . "!(role LIKE 'wolf%') AND !(role LIKE 'fox%')");
  $human = (int)mysql_result($sql, 0, 0);

  //狐の数を取得
  $sql = mysql_query($query_count . "role LIKE 'fox%'");
  $fox = (int)mysql_result($sql, 0, 0);

  //恋人の数を取得
  $sql = mysql_query($query_count . "role LIKE '%lovers%'");
  $lovers = (int)mysql_result($sql, 0, 0);

  if($wolf == 0 && $human == 0 && $fox == 0){ //全滅
    mysql_query($query_end_header . ", victory_role = 'vanish'" . $query_end_footer);
  }
  elseif($wolf == 0){ //狼全滅
    if($lovers > 1)
      $victory_role = 'lovers';
    elseif($fox == 0)
      $victory_role = 'human';
    else
      $victory_role = 'fox1';

    mysql_query($query_end_header . ", victory_role = '$victory_role'" . $query_end_footer);
  }
  elseif($wolf >= $human){ //村全滅
    if($lovers > 1)
      $victory_role = 'lovers';
    elseif($fox == 0)
      $victory_role = 'wolf';
    else
      $victory_role = 'fox2';

    mysql_query($query_end_header . ", victory_role = '$victory_role'" . $query_end_footer);
  }
  elseif($check_draw && $vote_times >= $GAME_CONF->draw){ //引き分け
    mysql_query($query_end_header . ", victory_role = 'draw'" . $query_end_footer);
  }
  mysql_query('COMMIT'); //一応コミット
}

//死亡処理
function DeadUser($target, $handle = false){
  global $room_no;

  if($handle) //HN 対応
    $query = "handle_name = '$target'";
  else
    $query = "uname = '$target'";

  mysql_query("UPDATE user_entry SET live = 'dead' WHERE room_no = $room_no
		AND $query AND user_no > 0");
}

//最終書き込み時刻を更新
function UpdateTime(){
  global $system_time, $room_no;
  mysql_query("UPDATE room SET last_updated = '$system_time' WHERE room_no = $room_no");
}

//今までの投票を全部削除
function DeleteVote(){
  global $room_no;
  mysql_query("DELETE FROM vote WHERE room_no = $room_no");
}

//システムメッセージ挿入 (talk Table)
function InsertSystemTalk($sentence, $time, $location = '', $target_date = '', $target_uname = 'system'){
  global $room_no, $date, $day_night;

  if($location    == '') $location = "$day_night system";
  if($target_date == '') $target_date = $date;
  InsertTalk($room_no, $target_date, $location, $target_uname, $time, $sentence, NULL, 0);
}

//システムメッセージ挿入 (system_message Table)
function InsertSystemMessage($sentence, $type, $target_date = ''){
  global $room_no, $date;

  if($target_date == '') $target_date = $date;
  mysql_query("INSERT INTO system_message(room_no, message, type, date)
		VALUES($room_no, '$sentence', '$type', $target_date)");
}

//恋人を調べるクエリ文字列を出力
function GetLoversConditionString($role) {
  $match_count = preg_match_all("/lovers\[\d+\]/", $role, $matches, PREG_PATTERN_ORDER);
  if ($match_count <= 0) return "";

  $val = $matches[0];
  $str = "( role LIKE '%$val[0]%'";
  for ($i = 1; $i < $match_count; $i++) {
    $str .= " OR role LIKE '%$val[$i]%'";
  }
  $str .= " )";
  return $str;
}

//恋人の後追い死処理
function LoversFollowed($role, $sudden_death = false){
  global $MESSAGE, $system_time, $room_no, $date, $day_night;

  // 後追いさせる必要がある恋人を取得
  $str_sql = "SELECT uname, role, handle_name, last_words FROM user_entry
			WHERE room_no = $room_no AND live = 'live' AND user_no > 0 AND ";
  $str_sql .= GetLoversConditionString($role);
  $sql = mysql_query($str_sql);

  $num_lovers = mysql_num_rows($sql);
  for ($i = 0; $i < $num_lovers; $i++) {
    $array = mysql_fetch_assoc($sql);
    $target_uname  = $array['uname'];
    $target_handle = $array['handle_name'];
    $target_last_words = $array['last_words'];
    $target_role  = $array['role'];

    DeadUser($target_uname);  //後追い死

    if ($sudden_death){ //突然死の処理
      InsertSystemTalk($target_handle . $MESSAGE->lovers_followed, ++$system_time);
    }
    else {
      //後追い死(システムメッセージ)
      InsertSystemMessage($target_handle, 'LOVERS_FOLLOWED_' . $day_night);

      //後追いした人の遺言を残す
      if($target_last_words != ''){
	InsertSystemMessage($target_handle . "\t" . $target_last_words, 'LAST_WORDS');
      }
    }
    //後追い連鎖処理
    LoversFollowed($target_role, $sudden_death);
  }
}

//スペースを復元する
function DecodeSpace(&$str){
  $str = str_replace("\\space;", ' ', $str);
}

//メッセージを分割して必要な情報を返す
function ParseStrings($str, $type = NULL){
  $str = str_replace(' ', "\\space;", $str); //スペースを退避する
  switch($type){
    case 'KICK_DO':
    case 'VOTE_DO':
    case 'WOLF_EAT':
    case 'MAGE_DO':
    case 'GUARD_DO':
    case 'CUPID_DO':
      sscanf($str, "{$type}\t%s", &$target);
      DecodeSpace($target);
      return $target;
      break;

    case 'MAGE_RESULT':
      sscanf($str, "%s\t%s\t%s", &$first, &$second, &$third);
      DecodeSpace($first);
      DecodeSpace($second);
      DecodeSpace($third);

      return array($first, $second, $third);
      break;

    case 'VOTE':
      sscanf($str, "%s\t%s\t%d\t%d\t%d", &$self, &$target, &$voted, &$vote, &$times);
      DecodeSpace($self);
      DecodeSpace($target);

      //%d で取得してるんだから (int)要らないような気がするんだけど……しかもなぜ一つだけ？
      return array($self, $target, $voted, $vote, (int)$times);
      break;

    default:
      sscanf($str, "%s\t%s", &$header, &$footer);
      DecodeSpace($header);
      DecodeSpace($footer);

      return array($header, $footer);
      break;
    }
}
?>
