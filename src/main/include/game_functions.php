<?php
//-- 基礎関数 --//
//配列からランダムに一つ取り出す
function GetRandom($array){ return $array[array_rand($array)]; }

//-- 時間関連 --//
//リアルタイムの経過時間
function GetRealPassTime(&$left_time){
  global $TIME_CONF, $ROOM;

  //シーンの最初の時刻を取得
  $query = 'SELECT MIN(time) FROM talk' . $ROOM->GetQuery() .
    " AND location LIKE '{$ROOM->day_night}%'";
  if(($start_time = FetchResult($query)) === false) $start_time = $ROOM->system_time;

  $base_time = $ROOM->real_time->{$ROOM->day_night} * 60; //設定された制限時間
  $pass_time = $ROOM->system_time - $start_time;
  if($ROOM->IsOption('wait_morning') && $ROOM->IsDay()){ //早朝待機制
    $base_time += $TIME_CONF->wait_morning; //制限時間を追加する
    $ROOM->event->wait_morning = $pass_time <= $TIME_CONF->wait_morning; //待機判定
  }
  if(($left_time = $base_time - $pass_time) < 0) $left_time = 0; //残り時間
  return array($start_time, $start_time + $base_time);
}

//会話で時間経過制の経過時間
function GetTalkPassTime(&$left_time, $silence = false){
  global $TIME_CONF, $ROOM;

  $query = 'SELECT SUM(spend_time) FROM talk' . $ROOM->GetQuery() .
    " AND location LIKE '{$ROOM->day_night}%'";
  $spend_time = (int)FetchResult($query);

  if($ROOM->IsDay()){ //昼は12時間
    $base_time = $TIME_CONF->day;
    $full_time = 12;
  }
  else{ //夜は6時間
    $base_time = $TIME_CONF->night;
    $full_time = 6;
  }
  if(($left_time = $base_time - $spend_time) < 0) $left_time = 0; //残り時間
  $base_left_time = $silence ? $TIME_CONF->silence_pass : $left_time; //仮想時間の計算
  return ConvertTime($full_time * $base_left_time * 60 * 60 / $base_time);
}

//-- 役職関連 --//
//巫女の判定結果 (システムメッセージ)
function InsertMediumMessage(){
  global $ROOM, $USERS;

  $flag = false; //巫女の出現判定
  $stack = array();
  foreach($USERS->rows as $user){
    $flag |= $user->IsRoleGroup('medium');
    if($user->suicide_flag){
      $virtual_user = $USERS->ByVirtual($user->user_no);
      $stack[$virtual_user->user_no] = $virtual_user->handle_name . "\t" . $user->GetCamp();
    }
  }
  if($flag){
    ksort($stack);
    foreach($stack as $str) $ROOM->SystemMessage($str, 'MEDIUM_RESULT');
  }
}

//恋人の後追い死処理
function LoversFollowed($sudden_death = false){
  global $MESSAGE, $ROOM, $USERS;

  $cupid_list      = array(); //キューピッドのID => 恋人のID
  $lost_cupid_list = array(); //恋人が死亡したキューピッドのリスト
  $checked_list    = array(); //処理済キューピッドのID

  foreach($USERS->rows as $user){ //キューピッドと死んだ恋人のリストを作成
    foreach($user->GetPartner('lovers', true) as $id){
      $cupid_list[$id][] = $user->user_no;
      if($user->dead_flag || $user->revive_flag) $lost_cupid_list[$id] = $id;
    }
  }

  while(count($lost_cupid_list) > 0){ //対象キューピッドがいれば処理
    $cupid_id = array_shift($lost_cupid_list);
    $checked_list[] = $cupid_id;
    foreach($cupid_list[$cupid_id] as $lovers_id){ //キューピッドのリストから恋人の ID を取得
      $user = $USERS->ById($lovers_id); //恋人の情報を取得
      if(! $USERS->Kill($user->user_no, 'LOVERS_FOLLOWED_' . $ROOM->day_night)) continue;
      if($sudden_death) $ROOM->Talk($user->handle_name . $MESSAGE->lovers_followed); //突然死の処理
      $user->suicide_flag = true;

      foreach($user->GetPartner('lovers') as $id){ //後追いした恋人のキューピッドのIDを取得
	if(! (in_array($id, $checked_list) || in_array($id, $lost_cupid_list))){ //連鎖判定
	  $lost_cupid_list[] = $id;
	}
      }
    }
  }
}

//勝敗をチェック
function CheckVictory($check_draw = false){
  global $GAME_CONF, $ROOM, $USERS;

  if($ROOM->test_mode) return false;

  //コピー能力者がいるのでキャッシュを更新するかクエリから引くこと
  $query_count = $ROOM->GetQuery(false, 'user_entry') . " AND live = 'live' AND user_no > 0 AND ";
  $human  = FetchResult($query_count . "!(role LIKE '%wolf%') AND !(role LIKE '%fox%')"); //村人
  $wolf   = FetchResult($query_count . "role LIKE '%wolf%'"); //人狼
  $fox    = FetchResult($query_count . "role LIKE '%fox%'"); //妖狐
  $lovers = FetchResult($query_count . "role LIKE '%lovers%'"); //恋人
  $quiz   = FetchResult($query_count . "role LIKE 'quiz%'"); //出題者

  //-- 吸血鬼の勝利判定 --//
  $vampire = false;
  $living_id_list = array(); //生存者の ID リスト
  $infected_list  = array(); //吸血鬼 => 感染者リスト
  foreach($USERS->GetLivingUsers(true) as $uname){
    $user = $USERS->ByUname($uname);
    $user->ReparseRoles();
    if(! $user->IsRole('psycho_infected')) $living_id_list[] = $user->user_no;
    if($user->IsRole('infected')){
      foreach($user->GetPartner('infected') as $id) $infected_list[$id][] = $user->user_no;
    }
  }
  if(count($living_id_list) == 1){
    $vampire = $USERS->ByID(array_shift($living_id_list))->IsRoleGroup('vampire');
  }
  else{
    foreach($infected_list as $id => $stack){
      $diff_list = array_diff($living_id_list, $stack);
      if(count($diff_list) == 1 && in_array($id, $diff_list)){
	$vampire = true;
	break;
      }
    }
  }

  $victory_role = ''; //勝利陣営
  if($human == $quiz && $wolf == 0 && $fox == 0) //全滅
    $victory_role = $quiz > 0 ? 'quiz' : 'vanish';
  elseif($vampire) //吸血鬼支配
    $victory_role = $lovers > 1 ? 'lovers' : 'vampire';
  elseif($wolf == 0) //狼全滅
    $victory_role = $lovers > 1 ? 'lovers' : ($fox > 0 ? 'fox1' : 'human');
  elseif($wolf >= $human) //村全滅
    $victory_role = $lovers > 1 ? 'lovers' : ($fox > 0 ? 'fox2' : 'wolf');
  elseif($human + $wolf + $fox == $lovers) //恋人支配
    $victory_role = 'lovers';
  elseif($ROOM->IsQuiz() && $quiz == 0) //クイズ村 GM 死亡
    $victory_role = 'quiz_dead';
  elseif($check_draw && $ROOM->GetVoteTimes() > $GAME_CONF->draw) //引き分け
    $victory_role = 'draw';

  if($victory_role == '') return false;

  //ゲーム終了
  $query = "UPDATE room SET status = 'finished', day_night = 'aftergame', " .
    "victory_role = '{$victory_role}', finish_time = NOW() WHERE room_no = {$ROOM->id}";
  SendQuery($query, true);
  //OutputSiteSummary(); //RSS機能はテスト中
  return true;
}

//-- 投票関連 --//
//夜の自分の投票先取得
function GetSelfVoteNight($situation, $not_situation = ''){
  global $ROOM, $SELF;

  $query = $ROOM->GetQueryHeader('vote', 'target_uname', 'situation') .
    ' AND date = ' . $ROOM->date . ' AND ';
  if($situation == 'WOLF_EAT'){
    $query .= "situation = '{$situation}'";
  }
  elseif($not_situation != ''){
    $query .= "uname = '{$SELF->uname}' " .
      "AND(situation = '{$situation}' OR situation = '{$not_situation}')";
  }
  else{
    $query .= "uname = '{$SELF->uname}' AND situation = '{$situation}'";
  }

  return FetchAssoc($query, true);
}

//夜の自分の投票済みチェック
function CheckSelfVoteNight($situation, $not_situation = ''){
  return count(GetSelfVoteNight($situation, $not_situation)) > 0;
}

//-- 出力関連 --//
//HTMLヘッダー出力
function OutputGamePageHeader(){
  global $SERVER_CONF, $GAME_CONF, $TIME_CONF, $RQ_ARGS, $ROOM, $SELF;

  //引数を格納
  $url_header = 'game_frame.php?room_no=' . $ROOM->id;
  if($RQ_ARGS->auto_reload > 0) $url_header .= '&auto_reload=' . $RQ_ARGS->auto_reload;
  if($RQ_ARGS->play_sound)      $url_header .= '&play_sound=on';
  if($RQ_ARGS->list_down)       $url_header .= '&list_down=on';

  $title = $SERVER_CONF->title . ' [プレイ]';
  $anchor_header = '<br>'."\n";
  /*
    Mac に JavaScript でエラーを吐くブラウザがあった当時のコード
    現在の Safari・Firefox では不要なので false でスキップしておく
    //if(preg_match('/Mac( OS|intosh|_PowerPC)/i', $_SERVER['HTTP_USER_AGENT'])){
  */
  if(false){
    $sentence = '';
    $anchor_header .= '<a href="';
    $anchor_footer = '" target="_top">ここをクリックしてください</a>';
  }
  else{
    $sentence = '<script type="text/javascript"><!--'."\n" .
      'if(top != self){ top.location.href = self.location.href; }'."\n" .
      '--></script>'."\n";
    $anchor_header .= '切り替わらないなら <a href="';
    $anchor_footer = '" target="_top">ここ</a>';
  }

  //ゲーム画面→天国モード (ゲーム中に死亡)
  if($ROOM->IsPlaying() && $SELF->IsDead() &&
     ! ($ROOM->log_mode || $ROOM->dead_mode || $ROOM->heaven_mode)){
    $jump_url = $url_header . '&dead_mode=on';
    $sentence .= '天国モードに切り替えます。';
  }
  elseif($ROOM->IsAfterGame() && $ROOM->dead_mode){ //天国モード→ゲーム終了画面
    $jump_url = $url_header;
    $sentence .= 'ゲーム終了後のお部屋に飛びます。';
  }
  elseif($SELF->IsLive() && ($ROOM->dead_mode || $ROOM->heaven_mode)){
    $jump_url = $url_header;
    $sentence .= 'ゲーム画面に飛びます。';
  }
  else $jump_url = '';

  if($jump_url != ''){ //移動先が設定されていたら画面切り替え
    $sentence .= $anchor_header . $jump_url . $anchor_footer;
    OutputActionResult($title, $sentence, $jump_url);
  }

  OutputHTMLHeader($title, 'game');
  echo '<link rel="stylesheet" href="css/game_' . $ROOM->day_night . '.css">'."\n";
  if(! $ROOM->log_mode){ //過去ログ閲覧時は不要
    echo '<script type="text/javascript" src="javascript/change_css.js"></script>'."\n";
    $on_load = "change_css('{$ROOM->day_night}');";
  }

  if($RQ_ARGS->auto_reload != 0 && ! $ROOM->IsAfterGame()){ //自動リロードをセット
    echo '<meta http-equiv="Refresh" content="' . $RQ_ARGS->auto_reload . '">'."\n";
  }

  //ゲーム中、リアルタイム制なら経過時間を Javascript でリアルタイム表示
  $game_top = '<a id="game_top"></a>';
  if($ROOM->IsPlaying() && $ROOM->IsRealTime() && ! ($ROOM->log_mode || $ROOM->heaven_mode)){
    list($start_time, $end_time) = GetRealPassTime($left_time);
    $sound_type = null;
    $alert_flag = false;
    $on_load .= 'output_realtime();';
    if($left_time < 1 && $SELF->IsLive()){ //超過判定
      $ROOM->LoadVote(); //投票情報を取得
      if($ROOM->IsDay()){ //未投票判定
	$novote_flag = ! in_array($SELF->uname, array_keys($ROOM->vote));
      }
      elseif($ROOM->IsNight()){
	$novote_flag = $SELF->CheckVote($ROOM->ParseVote()) === false;
      }

      if($novote_flag){
	$query = $ROOM->GetQueryHeader('room', 'UNIX_TIMESTAMP() - last_updated');
	if($TIME_CONF->alert > $TIME_CONF->sudden_death - FetchResult($query)){ //警告判定
	  $alert_flag = true;
	  $sound_type = 'alert';
	}
	else{
	  $sound_type = 'novote';
	}
      }
    }
    OutputRealTimer($start_time, $end_time, $sound_type, $alert_flag);
    $game_top .= "\n".'<span id="vote_alert"></span>';
  }
  $body = isset($on_load) ? '<body onLoad="' . $on_load . '">' : '<body>';
  echo '</head>'."\n".$body."\n".$game_top."\n";
}

//リアルタイム表示に使う JavaScript の変数を出力
function OutputRealTimer($start_time, $end_time, $type = null, $flag = false){
  global $TIME_CONF, $ROOM, $SOUND;

  $js_path     = JINRO_ROOT . '/javascript/';
  $sound_path  = is_null($type) || ! is_object($SOUND) ? '' : $SOUND->GenerateJS($type);
  $sentence    = '　' . ($ROOM->IsDay() ? '日没' : '夜明け') . 'まで ';
  $start_date  = GenerateJavaScriptDate($start_time);
  $end_date    = GenerateJavaScriptDate($end_time);
  $server_date = GenerateJavaScriptDate($ROOM->system_time);
  echo '<script type="text/javascript" src="' . $js_path . 'output_realtime.js"></script>'."\n";
  echo '<script language="JavaScript"><!--'."\n";
  echo 'var sentence = "' . $sentence . '";'."\n";
  echo "var end_date = {$end_date} * 1 + (new Date() - {$server_date});\n";
  echo "var diff_seconds = Math.floor(({$end_date} - {$start_date}) / 1000);\n";
  echo 'var sound_flag = ' . (is_null($type) ? 'false' : 'true') . ';'."\n";
  echo 'var countdown_flag = ' . ($flag ? 'true' : 'false') . ';'."\n";
  echo 'var sound_file = "' . $sound_path . '";'."\n";
  echo 'var alert_distance = "' . $TIME_CONF->alert_distance . '";'."\n";
  echo '// --></script>'."\n";
}

//JavaScript の Date() オブジェクト作成コードを生成する
function GenerateJavaScriptDate($time){
  $time_list = explode(',', TZDate('Y,m,j,G,i,s', $time));
  $time_list[1]--;  //JavaScript の Date() の Month は 0 からスタートする
  return 'new Date(' . implode(',', $time_list) . ')';
}

//自動更新のリンクを出力
function OutputAutoReloadLink($url){
  global $GAME_CONF, $RQ_ARGS;

  $str = '[自動更新](' . $url . '">' . ($RQ_ARGS->auto_reload > 0 ? '手動' : '【手動】') . '</a>';
  foreach($GAME_CONF->auto_reload_list as $time){
    $name = $time . '秒';
    $value = $RQ_ARGS->auto_reload == $time ? '【' . $name . '】' : $name;
    $str .= ' ' . $url . '&auto_reload=' . $time . '">' . $value . '</a>';
  }
  echo $str . ')'."\n";
}

//ログへのリンクを出力
function OutputLogLink(){
  global $ROOM;

  $url = 'old_log.php?room_no=' . $ROOM->id;
  echo GenerateLogLink($url, true, '<br>' . ($ROOM->view_mode ? '[ログ]' : '[全体ログ]')) .
    GenerateLogLink($url . '&add_role=on', false, '<br>[役職表示ログ]');
}

//ゲームオプション画像を出力
function OutputGameOption(){
  global $ROOM, $SELF;

  $query = $ROOM->GetQueryHeader('room', 'game_option', 'option_role', 'max_user');
  extract(FetchAssoc($query, true));
  echo '<div class="game-option">ゲームオプション：' .
    GenerateGameOptionImage($game_option, $option_role) .
    GenerateMaxUserImage($max_user) . '</div>'."\n";
}

//日付と生存者の人数を出力
function OutputTimeTable(){
  global $ROOM;

  echo '<table class="time-table"><tr>'."\n"; //ヘッダを表示

  if($ROOM->IsBeforeGame()) return false; //ゲームが始まっていなければスキップ
  $query = $ROOM->GetQuery(false, 'user_entry') . " AND live = 'live' AND user_no > 0";
  echo '<td>' . $ROOM->date . ' 日目<span>(生存者' . FetchResult($query) . '人)</span></td>'."\n";
}

//プレイヤー一覧生成
function GeneratePlayerList(){
  global $SERVER_CONF, $ICON_CONF, $ROOM, $USERS, $SELF;

  //PrintData($ROOM->event); //テスト用
  $beforegame = $ROOM->IsBeforeGame();
  $open_data  = $ROOM->IsOpenData(true);
  $count = 0; //改行カウントを初期化
  $str = '<div class="player"><table><tr>'."\n";
  foreach($USERS->rows as $id => $user){
    if($count > 0 && ($count % 5) == 0) $str .= "</tr>\n<tr>\n"; //5個ごとに改行
    $count++;

    //ゲーム開始投票をしていたら背景色を変える
    if($beforegame && ($user->IsDummyBoy(true) || isset($ROOM->vote[$user->uname])))
      $td_header = '<td class="already-vote">';
    else
      $td_header = '<td>';

    //ユーザプロフィールと枠線の色を追加
    //Alt, Title 内の改行はブラウザ依存あり (Firefox 系は無効)
    $profile = str_replace("\n", '&#13;&#10', $user->profile);
    $str .= $td_header . '<img title="' . $profile . '" alt="' . $profile .
      '" style="border-color: ' . $user->color . ';"';

    //生死情報に応じたアイコンを設定
    $path = $ICON_CONF->path . '/' . $user->icon_filename;
    if($beforegame || $ROOM->watch_mode || $USERS->IsVirtualLive($id)){
      $live = '(生存中)';
    }
    else{
      $live = '(死亡)';
      $str .= ' onMouseover="this.src=' . "'$path'" . '"'; //元のアイコン

      $path = $ICON_CONF->dead; //アイコンを死亡アイコンに入れ替え
      $str .= ' onMouseout="this.src=' . "'$path'" . '"';
    }
    if($ROOM->personal_mode){
      $live .= "<br>\n(" . GenerateVictory($user->user_no) . ')';
    }
    $str .= $ICON_CONF->tag . ' src="' . $path . '"></td>'."\n";

    //HN を追加
    $str .= $td_header . '<font color="' . $user->color . '">◆</font>' . $user->handle_name;
    if($SERVER_CONF->debug_mode) $str .= ' (' . $id . ')';
    $str .= '<br>'."\n";

    if($open_data){ //ゲーム終了後・死亡後＆霊界役職公開モードなら、役職・ユーザネームも表示
      $uname = str_replace(array('◆', '◇'), array('◆<br>', '◇<br>'), $user->uname); //トリップ対応
      $str .= '　(' . $uname; //ユーザ名を追加

      //憑依状態なら憑依しているユーザを追加
      $real_user = $USERS->ByReal($id);
      if($real_user->IsSame($user->uname)) $real_user = $USERS->TraceExchange($id); //交換憑依判定
      if(! $real_user->IsSame($user->uname) && $real_user->IsLive()){
	$str .= '<br>[' . $real_user->uname . ']';
      }
      $str .= ')<br>' . $user->GenerateRoleName() . '<br>'."\n"; //役職情報を追加
    }
    $str .= $live . '</td>'."\n";
  }
  return $str . '</tr></table></div>'."\n";
}

//プレイヤー一覧出力
function OutputPlayerList(){ echo GeneratePlayerList(); }

//勝敗結果の生成
function GenerateVictory($id = 0){
  global $VICT_MESS, $RQ_ARGS, $ROOM, $ROLES, $USERS, $SELF;

  //-- 村の勝敗結果 --//
  $victory = $ROOM->LoadVictory();
  $class   = $victory;
  $winner  = $victory;

  switch($victory){ //特殊ケース対応
  //妖狐勝利
  case 'fox1':
  case 'fox2':
    $victory = 'fox';
    $class   = $victory;
    break;

  //引き分け
  case 'draw': //引き分け
  case 'vanish': //全滅
  case 'quiz_dead': //クイズ村 GM 死亡
    $class = 'draw';
    break;

  //廃村
  case null:
    $class  = 'none';
    $winner = $ROOM->date > 0 ? 'unfinished' : 'none';
    break;
  }
  $str = <<<EOF
<table class="victory victory-{$class}"><tr>
<td>{$VICT_MESS->$winner}</td>
</tr></table>

EOF;

  //-- 個々の勝敗結果 --//
  //スキップ判定 (勝敗未決定/観戦モード/ログ閲覧モード)
  if(is_null($victory) || $ROOM->view_mode ||
     ($ROOM->log_mode && ! $ROOM->single_view_mode && ! $ROOM->personal_mode)){
    return $id > 0 ? '不明' : $str;
  }

  $result = 'win';
  $class  = null;
  $user   = $id > 0 ? $USERS->ByID($id) : $SELF;
  if($user->user_no < 1) return $str;
  $camp   = $user->GetCamp(true); //所属陣営を取得

  switch($victory){
  case 'draw':   //引き分け
  case 'vanish': //全滅
    $result = 'draw';
    $class  = $result;
    break;

  case 'quiz_dead': //出題者死亡
    $result = $camp == 'quiz' ? 'lose' : 'draw';
    $class  = $result;
    break;

  default:
    $ROLES->stack->class = null;
    switch($camp){
    case 'human':
    case 'wolf':
    case 'fox':
      $win_flag = $victory == $camp && $ROLES->LoadMain($user)->Win($victory);
      break;

    case 'vampire':
      $win_flag = $victory == $camp && ($SELF->IsRoleGroup('mania') || $user->IsLive());
      break;

    case 'chiroptera':
      $win_flag = $user->IsLive();
      break;

    case 'ogre':
    case 'duelist':
      $win_flag = $user->IsRoleGroup('mania') ? $user->IsLive()
	: $ROLES->LoadMain($user)->Win($victory);
      break;

    default:
      $win_flag = $victory == $camp;
      break;
    }

    if($win_flag){ //ジョーカー系判定
      $ROLES->actor = $user;
      foreach($ROLES->Load('joker') as $filter) $filter->FilterWin($win_flag);
    }

    if($win_flag){
      $class = is_null($ROLES->stack->class) ? $camp : $ROLES->stack->class;
    }
    else{
      $result = 'lose';
      $class  = $result;
    }
    break;
  }
  if($id > 0){
    switch($result){
    case 'win':
      return '勝利';

    case 'lose':
      return '敗北';

    case 'draw':
      return '引分';

    case 'win':
      return '不明';
    }
  }
  $result = 'self_' . $result;

  return $str . <<<EOF
<table class="victory victory-{$class}"><tr>
<td>{$VICT_MESS->$result}</td>
</tr></table>

EOF;
}

//勝敗結果の出力
function OutputVictory(){ echo GenerateVictory(); }

//投票の集計生成
function GenerateVoteResult(){
  global $MESSAGE, $ROOM;

  if(! $ROOM->IsPlaying()) return null; //ゲーム中以外は出力しない
  if($ROOM->IsEvent('blind_vote') && ! $ROOM->IsOpenData()) return null; //傘化けの判定

  //昼なら前日、夜ならの今日の集計を表示
  return GetVoteList(($ROOM->IsDay() && ! $ROOM->log_mode) ? $ROOM->date - 1 : $ROOM->date);
}

//投票の集計出力
function OutputVoteList(){
  if(is_null($str = GenerateVoteResult())) return false;
  echo $str;
}

//再投票の時、メッセージを表示
function OutputRevoteList(){
  global $GAME_CONF, $MESSAGE, $RQ_ARGS, $ROOM, $SELF, $COOKIE, $SOUND;

  if(! $ROOM->IsDay()) return false; //昼以外は出力しない
  if(($revote_times = $ROOM->GetVoteTimes(true)) == 0) return false; //再投票の回数を取得

  if($RQ_ARGS->play_sound && ! $ROOM->view_mode && $revote_times > $COOKIE->vote_times){
    $SOUND->Output('revote'); //音を鳴らす
  }

  //投票済みチェック
  $vote_times = $revote_times + 1;
  $query = $ROOM->GetQuery(true, 'vote') . " AND vote_times = {$vote_times} " .
    "AND uname = '{$SELF->uname}'";
  if(FetchResult($query) == 0){
    echo '<div class="revote">' . $MESSAGE->revote . ' (' . $GAME_CONF->draw . '回' .
      $MESSAGE->draw_announce . ')</div><br>';
  }

  echo GetVoteList($ROOM->date); //投票結果を出力
}

//指定した日付の投票結果をロードして GenerateVoteList() に渡す
function GetVoteList($date){
  global $ROOM;

  if($ROOM->personal_mode) return null; //スキップ判定
  //指定された日付の投票結果を取得
  $query = $ROOM->GetQueryHeader('system_message', 'message') .
    " AND date = {$date} and type = 'VOTE_KILL'";
  return GenerateVoteList(FetchArray($query), $date);
}

//投票データから結果を生成する
function GenerateVoteList($raw_data, $date){
  global $RQ_ARGS, $ROOM, $SELF;

  if(count($raw_data) < 1) return null; //投票総数

  $open_vote = $ROOM->IsOpenData() || $ROOM->IsOption('open_vote'); //投票数開示判定
  $table_stack = array();
  $header = '<td class="vote-name">';
  foreach($raw_data as $raw){ //個別投票データのパース
    list($handle_name, $target_name, $voted_number,
	 $vote_number, $vote_times) = explode("\t", $raw);

    $stack = array('<tr>' .  $header . $handle_name, '<td>' . $voted_number . ' 票',
		   '<td>投票先' . ($open_vote ? ' ' . $vote_number . ' 票' : '') . ' →',
		   $header . $target_name, '</tr>');
    $table_stack[$vote_times][] = implode('</td>', $stack);
  }
  if(! $RQ_ARGS->reverse_log) krsort($table_stack); //正順なら逆転させる
  $str = '';
  $header = '<tr><td class="vote-times" colspan="4">' . $date . ' 日目 ( ';
  $footer = ' 回目)</td>';
  foreach($table_stack as $vote_times => $stack){
    array_unshift($stack, '<table class="vote-list">', $header . $vote_times . $footer);
    $stack[] = '</table>';
    $str .= implode("\n", $stack);
  }
  return $str;
}

//会話ログ出力
function OutputTalkLog(){
  global $ROOM;

  $builder = new DocumentBuilder();
  $builder->BeginTalk('talk');
  foreach($ROOM->LoadTalk() as $talk) OutputTalk($talk, $builder); //会話出力
  OutputTimeStamp($builder);
  $builder->EndTalk();
}

//会話出力
function OutputTalk($talk, &$builder){
  global $MESSAGE, $RQ_ARGS, $ROOM, $ROLES, $USERS, $SELF;

  //PrintData($talk);
  //発言ユーザを取得
  /*
    $uname は必ず $talk から取得すること。
    $USERS にはシステムユーザー 'system' が存在しないため、$actor は常に null になっている。
  */
  $actor = $talk->scene == 'heaven' ? $USERS->ByUname($talk->uname) :
    $USERS->ByVirtualUname($talk->uname);

  //基本パラメータを取得
  if($talk->uname == 'system'){
    $symbol = '';
    $name   = '';
    $actor->user_no = 0;
  }
  else{
    $symbol = '<font color="' . $actor->color . '">◆</font>';
    $name   = $actor->handle_name;
  }

  //実ユーザを取得
  if($RQ_ARGS->add_role && $actor->user_no > 0){ //役職表示モード対応
    $real_user = $talk->scene == 'heaven' ? $actor : $USERS->ByReal($actor->user_no);
    $name .= $real_user->GenerateShortRoleName($talk->scene == 'heaven');
  }
  else{
    $real_user = $USERS->ByRealUname($talk->uname);
  }

  if($talk->type == 'system' && isset($talk->action)){ //投票情報
    switch($talk->action){
    case 'GAMESTART_DO': //現在は不使用
      return true;

    case 'OBJECTION': //「異議」ありは常時表示
      return $builder->AddSystemMessage('objection-' . $actor->sex, $name . $talk->sentence);

    default: //ゲーム開始前の投票 (例：KICK) は常時表示
      return $builder->flag->open_talk || $ROOM->IsBeforeGame() ?
	$builder->AddSystemMessage($talk->class, $name . $talk->sentence) : false;
    }
  }
  if($talk->uname == 'system') return $builder->AddSystemTalk($talk->sentence); //システムメッセージ

  //身代わり君専用システムメッセージ
  if($talk->type == 'dummy_boy') return $builder->AddSystemTalk($talk->sentence, 'dummy-boy');

  switch($talk->scene){
  case 'day':
    //強風判定 (身代わり君と本人は対象外)
    if($ROOM->IsEvent('blind_talk_day') &&
       ! $builder->flag->dummy_boy && ! $builder->actor->IsSame($talk->uname)){
      //位置判定 (観戦者以外の上下左右)
      $viewer = $builder->actor->user_no;
      $target = $actor->user_no;
      if(is_null($viewer) ||
	 ! (abs($target - $viewer) == 5 ||
	    ($target == $viewer - 1 && ($target % 5) != 0) ||
	    ($target == $viewer + 1 && ($viewer % 5) != 0))){
	$talk->sentence = $MESSAGE->common_talk;
      }
    }
    return $builder->AddTalk($actor, $talk);

  case 'night':
    if($builder->flag->open_talk){
      $class = '';
      $voice = $talk->font_type;
      switch($talk->type){
      case 'common':
	$name .= '<span>(共有者)</span>';
	$class = 'night-common';
	$voice .= ' ' . $class;
	break;

      case 'wolf':
	$name .= '<span>(人狼)</span>';
	$class = 'night-wolf';
	$voice .= ' ' . $class;
	break;

      case 'mad':
	$name .= '<span>(囁き狂人)</span>';
	$class = 'night-wolf';
	$voice .= ' ' . $class;
	break;

      case 'fox':
	$name .= '<span>(妖狐)</span>';
	$class = 'night-fox';
	$voice .= ' ' . $class;
	break;

      case 'self_talk':
	$name .= '<span>の独り言</span>';
	$class = 'night-self-talk';
	break;
      }
      return $builder->RawAddTalk($symbol, $name, $talk->sentence, $voice, '', $class);
    }
    else{
      $mind_read = false; //特殊発言透過判定
      $ROLES->actor = $actor;
      foreach($ROLES->Load('mind_read') as $filter) $mind_read |= $filter->IsMindRead();

      $ROLES->actor = $builder->actor;
      foreach($ROLES->Load('mind_read_active') as $filter){
	$mind_read |= $filter->IsMindReadActive($actor);
      }

      $ROLES->actor = $real_user;
      foreach($ROLES->Load('mind_read_possessed') as $filter){
	$mind_read |= $filter->IsMindReadPossessed($actor);
      }

      $ROLES->actor = $actor;
      switch($talk->type){
      case 'common': //共有者
	if($builder->flag->common || $mind_read) return $builder->AddTalk($actor, $talk);
	if($ROLES->LoadMain($actor)->Whisper($builder, $talk->font_type)) return;
	foreach($ROLES->Load('talk_whisper') as $filter){
	  if($filter->Whisper($builder, $talk->font_type)) return;
	}
	return false;

      case 'wolf': //人狼
	if($builder->flag->wolf || $mind_read) return $builder->AddTalk($actor, $talk);
	if($ROLES->LoadMain($actor)->Howl($builder, $talk->font_type)) return;
	foreach($ROLES->Load('talk_whisper') as $filter){
	  if($filter->Whisper($builder, $talk->font_type)) return;
	}
	return false;

      case 'mad': //囁き狂人
	if($builder->flag->wolf || $mind_read) return $builder->AddTalk($actor, $talk);
	foreach($ROLES->Load('talk_whisper') as $filter){
	  if($filter->Whisper($builder, $talk->font_type)) return;
	}
	return false;

      case 'fox': //妖狐
	if($builder->flag->fox || $mind_read) return $builder->AddTalk($actor, $talk);
	$ROLES->actor = $SELF;
	foreach($ROLES->Load('talk_fox') as $filter){
	  if($filter->Whisper($builder, $talk->font_type)) return;
	}
	$ROLES->actor = $actor;
	foreach($ROLES->Load('talk_whisper') as $filter){
	  if($filter->Whisper($builder, $talk->font_type)) return;
	}
	return false;

      case 'self_talk': //独り言
	if($builder->flag->dummy_boy || $mind_read || $builder->actor->IsSame($talk->uname)){
	  return $builder->AddTalk($actor, $talk);
	}
	foreach($ROLES->Load('talk_self') as $filter){
	  if($filter->Whisper($builder, $talk->font_type)) return;
	}
	$ROLES->actor = $builder->actor;
	foreach($ROLES->Load('talk_ringing') as $filter){
	  if($filter->Whisper($builder, $talk->font_type)) return;
	}
	return false;
      }
    }
    return false;

  case 'heaven':
    return ! $builder->flag->open_talk ? false :
      $builder->RawAddTalk($symbol, $name, $talk->sentence, $talk->font_type, $talk->scene);

  default:
    return $builder->AddTalk($actor, $talk);
  }
}

//天国の霊話ログ出力
function OutputHeavenTalkLog(){
  global $ROOM, $USERS;

  //出力条件をチェック
  //if($SELF->IsDead()) return false; //呼び出し側でチェックするので現在は不要

  $is_open = $ROOM->IsOpenCast(); //霊界公開判定
  $builder = new DocumentBuilder();
  $builder->BeginTalk('talk');
  foreach($ROOM->LoadTalk(true) as $talk){
    $user = $USERS->ByUname($talk->uname); //ユーザを取得

    $symbol = '<font color="' . $user->color . '">◆</font>';
    $handle_name = $user->handle_name;
    if($is_open) $handle_name .= '<span>(' . $talk->uname . ')</span>'; //HN 追加処理

    $builder->RawAddTalk($symbol, $handle_name, $talk->sentence, $talk->font_type);
  }
  $builder->EndTalk();
}

//[村立て / ゲーム開始 / ゲーム終了] 時刻を出力
function OutputTimeStamp($builder){
  global $ROOM;

  $talk = new Talk();
  if($ROOM->IsBeforeGame()){ //村立て時刻を取得して表示
    $type = 'establish_time';
    $talk->sentence = '村作成';
  }
  elseif($ROOM->IsNight() && $ROOM->date == 1){ //ゲーム開始時刻を取得して表示
    $type = 'start_time';
    $talk->sentence = 'ゲーム開始';
  }
  elseif($ROOM->IsAfterGame()){ //ゲーム終了時刻を取得して表示
    $type = 'finish_time';
    $talk->sentence = 'ゲーム終了';
  }
  else return false;

  if(is_null($time = FetchResult($ROOM->GetQueryHeader('room', $type)))) return false;
  $talk->uname = 'system';
  $talk->sentence .= '：' . ConvertTimeStamp($time);
  $talk->ParseLocation($ROOM->day_night . ' system');
  OutputTalk($talk, $builder);
}

//前日の能力発動結果を出力
function OutputAbilityAction(){
  global $MESSAGE, $RQ_ARGS, $ROOM, $USERS, $SELF;

  //昼間で役職公開が許可されているときのみ表示
  if(! $ROOM->IsDay() || ! ($SELF->IsDummyBoy() || $ROOM->IsOpenCast())) return false;

  $header = '<b>前日の夜、';
  $footer = '</b><br>'."\n";
  if($ROOM->test_mode){
    $stack_list = $RQ_ARGS->TestItems->ability_action_list;
  }
  else{
    $yesterday = $ROOM->date - 1;
    $action_list = array('WOLF_EAT', 'MAGE_DO', 'VOODOO_KILLER_DO', 'MIND_SCANNER_DO',
			 'JAMMER_MAD_DO', 'VOODOO_MAD_DO', 'VOODOO_FOX_DO', 'CHILD_FOX_DO',
			 'FAIRY_DO');
    if($yesterday == 1){
      array_push($action_list, 'CUPID_DO', 'DUELIST_DO', 'MANIA_DO');
    }
    else{
      array_push($action_list, 'GUARD_DO', 'ANTI_VOODOO_DO', 'REPORTER_DO', 'WIZARD_DO',
		 'SPREAD_WIZARD_DO', 'ESCAPE_DO', 'DREAM_EAT', 'ASSASSIN_DO', 'ASSASSIN_NOT_DO',
		 'POISON_CAT_DO', 'POISON_CAT_NOT_DO', 'TRAP_MAD_DO', 'TRAP_MAD_NOT_DO',
		 'POSSESSED_DO', 'POSSESSED_NOT_DO', 'VAMPIRE_DO', 'OGRE_DO', 'OGRE_NOT_DO',
		 'DEATH_NOTE_DO', 'DEATH_NOTE_NOT_DO');
    }
    $action = '';
    foreach($action_list as $this_action){
      if($action != '') $action .= ' OR ';
      $action .= "type = '$this_action'";
    }
    $query = $ROOM->GetQueryHeader('system_message', 'message', 'type') .
      " AND date = {$yesterday} AND ({$action})";
    $stack_list = FetchAssoc($query);
  }

  foreach($stack_list as $stack){
    list($actor, $target) = explode("\t", $stack['message']);
    echo $header.$USERS->ByHandleName($actor)->GenerateShortRoleName(false, true).' ';
    switch($stack['type']){
    case 'CUPID_DO': //DB 登録時にタブ区切りで登録していないので個別の名前は取得不可
    case 'FAIRY_DO':
    case 'DUELIST_DO':
    case 'SPREAD_WIZARD_DO':
      $target = 'は '.$target;
      break;

    case 'SPREAD_WIZARD_DO': //テストコード (現在は不使用)
      $str_stack = array();
      foreach(explode(' ', $target) as $id){
	$str_stack[] = $USERS->ByID($id)->GenerateShortRoleName(false, true);
      }
      $target = 'は '.implode(' ', $str_stack);
      break;

    default:
      $target = 'は '.$USERS->ByHandleName($target)->GenerateShortRoleName(false, true).' ';
      break;
    }

    switch($stack['type']){
    case 'GUARD_DO':
    case 'REPORTER_DO':
    case 'ASSASSIN_DO':
    case 'WIZARD_DO':
    case 'ESCAPE_DO':
    case 'WOLF_EAT':
    case 'DREAM_EAT':
    case 'CUPID_DO':
    case 'VAMPIRE_DO':
    case 'FAIRY_DO':
    case 'OGRE_DO':
    case 'DUELIST_DO':
    case 'DEATH_NOTE_DO':
      echo $target.$MESSAGE->{strtolower($stack['type'])};
      break;

    case 'ASSASSIN_NOT_DO':
    case 'POSSESSED_NOT_DO':
    case 'OGRE_NOT_DO':
    case 'DEATH_NOTE_NOT_DO':
      echo $MESSAGE->{strtolower($stack['type'])};
      break;

    case 'POISON_CAT_DO':
      echo $target.$MESSAGE->revive_do;
      break;

    case 'POISON_CAT_NOT_DO':
      echo $MESSAGE->revive_not_do;
      break;

    case 'SPREAD_WIZARD_DO':
      echo $target.$MESSAGE->wizard_do;
      break;

    case 'TRAP_MAD_DO':
      echo $target.$MESSAGE->trap_do;
      break;

    case 'TRAP_MAD_NOT_DO':
      echo $MESSAGE->trap_not_do;
      break;

    case 'MAGE_DO':
    case 'CHILD_FOX_DO':
      echo $target.'を占いました';
      break;

    case 'VOODOO_KILLER_DO':
      echo $target.'の呪いを祓いました';
      break;

    case 'ANTI_VOODOO_DO':
      echo $target.'の厄を祓いました';
      break;

    case 'MIND_SCANNER_DO':
      echo $target.'の心を読みました';
      break;

    case 'JAMMER_MAD_DO':
      echo $target.'の占いを妨害しました';
      break;

    case 'VOODOO_MAD_DO':
    case 'VOODOO_FOX_DO':
      echo $target.'に呪いをかけました';
      break;

    case 'POSSESSED_DO':
      echo $target.'を狙いました';
      break;

    case 'MANIA_DO':
      echo $target.'を真似しました';
      break;
    }
    echo $footer;
  }
}

//死亡者の遺言を生成
function GenerateLastWords($shift = false){
  global $MESSAGE, $ROOM;

  if(! ($ROOM->IsPlaying() || $ROOM->log_mode) || $ROOM->personal_mode) return null; //スキップ判定

  //前日の死亡者遺言を出力
  $set_date = $ROOM->date - 1;
  if($shift) $set_date++;
  $query = $ROOM->GetQueryHeader('system_message', 'message') .
    " AND date = {$set_date} AND type = 'LAST_WORDS' ORDER BY RAND()";
  $array = FetchArray($query);
  if(count($array) < 1) return null;

  $str = <<<EOF
<table class="system-lastwords"><tr>
<td>{$MESSAGE->lastwords}</td>
</tr></table>
<table class="lastwords">

EOF;

  foreach($array as $result){
    list($handle_name, $sentence) = explode("\t", $result, 2);
    LineToBR($sentence);

    $str .= <<<EOF
<tr>
<td class="lastwords-title">{$handle_name}<span>さんの遺言</span></td>
<td class="lastwords-body">{$sentence}</td>
</tr>

EOF;
  }
  return $str . '</table>'."\n";
}

//死亡者の遺言を出力
function OutputLastWords($shift = false){
  if(is_null($str = GenerateLastWords($shift))) return false;
  echo $str;
}

//前の日の 狼が食べた、狐が占われて死亡、投票結果で死亡のメッセージ
function GenerateDeadMan(){
  global $ROOM;

  //ゲーム中以外は出力しない
  if(! $ROOM->IsPlaying()) return null;

  $yesterday = $ROOM->date - 1;

  //共通クエリ
  $query_header = $ROOM->GetQueryHeader('system_message', 'message', 'type') . " AND date =";

  //死亡タイプリスト
  $dead_type_list = array(
    'day' => array(
      'VOTE_KILLED' => true, 'BLIND_VOTE' => true, 'POISON_DEAD_day' => true,
      'LOVERS_FOLLOWED_day' => true, 'SUDDEN_DEATH_%' => false, 'NOVOTED_day' => true,
      'JOKER_MOVED_day' => true),

    'night' => array(
      'WOLF_KILLED' => true, 'HUNGRY_WOLF_KILLED' => true, 'POSSESSED' => true,
      'POSSESSED_TARGETED' => true, 'POSSESSED_RESET' => true, 'DREAM_KILLED' => true,
      'TRAPPED' => true, 'CURSED' => true, 'FOX_DEAD' => true, 'HUNTED' => true,
      'REPORTER_DUTY' => true, 'VAMPIRE_KILLED' => true, 'ASSASSIN_KILLED' => true,
      'ESCAPER_DEAD' => true, 'OGRE_KILLED' => true, 'PRIEST_RETURNED' => true,
      'POISON_DEAD_night' => true, 'LOVERS_FOLLOWED_night' => true, 'REVIVE_%' => false,
      'SACRIFICE' => true, 'FLOWERED_%' => false, 'CONSTELLATION_%' => false, 'PIERROT_%' => false,
      'NOVOTED_night' => true, 'JOKER_MOVED_night' => true, 'DEATH_NOTE_MOVED' => true));

  foreach($dead_type_list as $scene => $action_list){
    $query_list = array();
    foreach($action_list as $action => $type){
      $query_list[] = 'type ' . ($type ? '=' : 'LIKE') . " '{$action}'";
    }
    $type_list->$scene = implode(' OR ', $query_list);
  }

  if($ROOM->IsDay()){
    $set_date = $yesterday;
    $type = $type_list->night;
  }
  else{
    $set_date = $ROOM->date;
    $type = $type_list->day;
  }

  $str = GenerateWeatherReport();
  foreach(FetchAssoc("{$query_header} {$set_date} AND ({$type}) ORDER BY RAND()") as $stack){
    $str .= GenerateDeadManType($stack['message'], $stack['type']);
  }

  //ログ閲覧モード以外なら二つ前も死亡者メッセージ表示
  if($ROOM->log_mode) return $str;
  $set_date = $yesterday;
  if($set_date < 2) return $str;
  $type = $type_list->{$ROOM->day_night};

  $str .= '<hr>'; //死者が無いときに境界線を入れない仕様にする場合はクエリの結果をチェックする
  foreach(FetchAssoc("{$query_header} {$set_date} AND ({$type}) ORDER BY RAND()") as $stack){
    $str .= GenerateDeadManType($stack['message'], $stack['type']);
  }
  return $str;
}

//天候メッセージの生成
function GenerateWeatherReport(){
  global $ROLE_DATA, $RQ_ARGS, $ROOM;

  if(! property_exists($ROOM->event, 'weather') || is_null($ROOM->event->weather) ||
     ($ROOM->log_mode && $RQ_ARGS->reverse_log && $ROOM->IsNight())) return '';

  $weather = $ROLE_DATA->weather_list[$ROOM->event->weather];
  return '<div class="weather">今日の天候は<span>' . $weather['name'] . '</span>です (' .
    $weather['caption'] . ')</div>';
}

//前日に死亡メッセージの出力
function OutputDeadMan(){
  if(is_null($str = GenerateDeadMan())) return false;
  echo $str;
}

//死者のタイプ別に死亡メッセージを生成
function GenerateDeadManType($name, $type){
  global $MESSAGE, $ROOM, $SELF;

  //タイプの解析
  $base_type    = $type;
  $parsed_type  = explode('_', $type);
  $footer_type  = array_pop($parsed_type);
  $implode_type = implode('_', $parsed_type);
  switch($footer_type){
  case 'day':
  case 'night':
    $base_type = $implode_type;
    break;

  default:
    switch($implode_type){
    case 'SUDDEN_DEATH':
    case 'FLOWERED':
    case 'CONSTELLATION':
    case 'PIERROT':
      $base_type = $implode_type;
      break;
    }
    break;
  }

  $name .= ' ';
  $base   = true;
  $class  = null;
  $reason = null;
  $action = strtolower($base_type);
  $open_reason = $ROOM->IsOpenData();
  $show_reason = $open_reason || $SELF->IsLiveRole('yama_necromancer');
  $str = '<table class="dead-type">'."\n";
  switch($base_type){
  case 'VOTE_KILLED':
    $base  = false;
    $class = 'vote';
    break;

  case 'BLIND_VOTE':
    $name  = '';
    $base  = false;
    $class = 'vote';
    break;

  case 'LOVERS_FOLLOWED':
    $base  = false;
    $class = 'lovers';
    break;

  case 'REVIVE_SUCCESS':
    $base  = false;
    $class = 'revive';
    break;

  case 'REVIVE_FAILED':
    if(! $ROOM->IsFinished() && ! ($SELF->IsDead() || $SELF->IsRole('attempt_necromancer'))) return;
    $base  = false;
    $class = 'revive';
    break;

  case 'POSSESSED_TARGETED':
    if(! $open_reason) return;
    $base = false;
    break;

  case 'NOVOTED':
    $base  = false;
    $class = 'sudden-death';
    break;

  case 'SUDDEN_DEATH':
    $base   = false;
    $class  = 'sudden-death';
    $action = 'vote_sudden_death';
    if($show_reason) $reason = strtolower($footer_type);
    break;

  case 'FLOWERED':
  case 'CONSTELLATION':
  case 'PIERROT':
    $base   = false;
    $class  = 'fairy';
    $action = strtolower($type);
    break;

  case 'JOKER_MOVED':
  case 'DEATH_NOTE_MOVED':
    if(! $open_reason) return;
    $base  = false;
    $class = 'fairy';
    break;

  default:
    if($show_reason) $reason = $action;
    break;
  }
  $str .= is_null($class) ? '<tr>' : '<tr class="dead-type-'.$class.'">';
  $str .= '<td>'.$name.$MESSAGE->{$base ? 'deadman' : $action}.'</td>';
  if(isset($reason)) $str .= "</tr>\n<tr><td>(".$name.$MESSAGE->$reason.')</td>';
  return $str."</tr>\n</table>\n";
}
