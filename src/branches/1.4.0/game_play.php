<?php
require_once('include/init.php');
$INIT_CONF->LoadFile('game_play_functions', 'user_class', 'talk_class');
$INIT_CONF->LoadClass('SESSION', 'ROLES', 'ICON_CONF', 'TIME_CONF', 'ROOM_IMG');

//-- データ収集 --//
$INIT_CONF->LoadRequest('RequestGamePlay'); //引数を取得
if($RQ_ARGS->play_sound) $INIT_CONF->LoadClass('SOUND', 'COOKIE'); //音でお知らせ

$DB_CONF->Connect(); //DB 接続
$SESSION->CertifyGamePlay(); //セッション認証

$ROOM = new Room($RQ_ARGS); //村情報をロード
$ROOM->dead_mode    = $RQ_ARGS->dead_mode; //死亡者モード
$ROOM->heaven_mode  = $RQ_ARGS->heaven_mode; //霊話モード
$ROOM->system_time  = TZTime(); //現在時刻を取得
$ROOM->sudden_death = 0; //突然死実行までの残り時間

$USERS = new UserDataSet($RQ_ARGS); //ユーザ情報をロード
$SELF = $USERS->BySession(); //自分の情報をロード

//シーンに応じた追加クラスをロード
if($ROOM->IsBeforeGame()){ //ゲームオプション表示
  $INIT_CONF->LoadClass('ROOM_CONF', 'CAST_CONF', 'ROOM_IMG', 'GAME_OPT_MESS');
  $ROOM->LoadVote();
}
elseif($ROOM->IsFinished()){ //勝敗結果表示
  $INIT_CONF->LoadClass('VICT_MESS');
}
SendCookie($OBJECTION); //必要なクッキーをセットする

//-- 発言処理 --//
$say_limit = null;
if(! $ROOM->dead_mode || $ROOM->heaven_mode){ //発言が送信されるのは bottom フレーム
  $say_limit = ConvertSay($RQ_ARGS->say); //発言置換処理

  if($RQ_ARGS->say == ''){
    CheckSilence(); //発言が空ならゲーム停滞のチェック(沈黙、突然死)
  }
  elseif($RQ_ARGS->last_words && ! $SELF->IsDummyBoy()){
    EntryLastWords($RQ_ARGS->say); //遺言登録 (細かい判定条件は関数内で行う)
  }
  elseif($SELF->IsDead() || $SELF->IsDummyBoy() || $SELF->last_load_day_night == $ROOM->day_night){
    Say($RQ_ARGS->say); //死んでいる or 身代わり君 or ゲームシーンが一致しているなら書き込む
  }
  else{
    CheckSilence(); //発言ができない状態ならゲーム停滞チェック
  }

  if($SELF->last_load_day_night != $ROOM->day_night){ //ゲームシーンを更新
    $SELF->Update('last_load_day_night', $ROOM->day_night);
  }
}
elseif($ROOM->dead_mode && $ROOM->IsPlaying() && $SELF->IsDummyBoy()){
  SetSuddenDeathTime();
}

//-- データ出力 --//
OutputGamePageHeader(); //HTMLヘッダ
OutputGameHeader(); //部屋のタイトルなど
if($say_limit === false) echo '<font color="#FF0000">' . $MESSAGE->say_limit . '</font><br>';
if(! $ROOM->heaven_mode){
  if(! $RQ_ARGS->list_down) OutputPlayerList(); //プレイヤーリスト
  OutputAbility(); //自分の役割の説明
  if($ROOM->IsDay() && $SELF->IsLive() && $ROOM->date != 1) CheckSelfVoteDay(); //昼の投票済みチェック
  OutputRevoteList(); //再投票の時、メッセージを表示する
}

//会話ログを出力
($SELF->IsDead() && $ROOM->heaven_mode) ? OutputHeavenTalkLog() : OutputTalkLog();

if(! $ROOM->heaven_mode){
  if($SELF->IsDead()) OutputAbilityAction(); //能力発揮
  OutputLastWords(); //遺言
  OutputDeadMan();   //死亡者
  OutputVoteList();  //投票結果
  if(! $ROOM->dead_mode) OutputSelfLastWords(); //自分の遺言
  if($RQ_ARGS->list_down) OutputPlayerList(); //プレイヤーリスト
}
OutputHTMLFooter();

//-- 関数 --//
//必要なクッキーをまとめて登録(ついでに最新の異議ありの状態を取得して配列に格納)
function SendCookie(&$objection_list){
  global $GAME_CONF, $RQ_ARGS, $ROOM, $USERS, $SELF;

  //-- 夜明け --
  //クッキーに格納 (夜明けに音でお知らせで使う・有効期限一時間)
  setcookie('day_night', $ROOM->day_night, $ROOM->system_time + 3600);

  //-- 再投票 --//
  //再投票の回数を取得
  if(($last_vote_times = $ROOM->GetVoteTimes(true)) > 0){ //クッキーに格納 (有効期限一時間)
    setcookie('vote_times', $last_vote_times, $ROOM->system_time + 3600);
  }
  else{ //クッキーから削除 (有効期限一時間)
    setcookie('vote_times', '', $ROOM->system_time - 3600);
  }

  //-- 入村情報 --//
  if($ROOM->IsBeforeGame()){
    setcookie('user_count', $USERS->GetUserCount(), $ROOM->system_time + 3600);
  }

  //-- 「異議」あり --//
  $user_count = $USERS->GetUserCount(true); //KICK も含めたユーザ総数を取得
  $objection_list = array_fill(0, $user_count, 0); //配列をセット (index は 0 から)

  //「異議」ありをしたユーザ No とその回数を取得
  $query = 'SELECT message, COUNT(message) AS count FROM system_message' . $ROOM->GetQuery(false) .
    " AND type = 'OBJECTION' GROUP BY message";
  foreach(FetchAssoc($query) as $stack){
    $objection_list[(int)$stack['message'] - 1] = (int)$stack['count'];
  }

  //「異議」ありセット判定
  if($RQ_ARGS->set_objection && $objection_list[$SELF->user_no - 1] < $GAME_CONF->objection &&
     ($ROOM->IsBeforeGame() || ($SELF->IsLive() && $ROOM->IsDay()))){
    $ROOM->SystemMessage($SELF->user_no, 'OBJECTION');
    $ROOM->Talk('OBJECTION', $SELF->uname);
    $objection_list[$SELF->user_no - 1]++; //使用回数をインクリメント
  }
  //クッキーに格納 (有効期限一時間)
  setcookie('objection', implode(',', $objection_list), $ROOM->system_time + 3600);
  $SELF->objection_count = $objection_list[$SELF->user_no - 1]; //残り異議ありの回数をセット
}

//発言置換処理
function ConvertSay(&$say){
  global $GAME_CONF, $MESSAGE, $ROOM, $ROLES, $USERS, $SELF;

  if($say == '') return true; //リロード時なら処理スキップ
  //文字数・行数チェック
  if(strlen($say) > $GAME_CONF->say_limit ||
     substr_count($say, "\n") >= $GAME_CONF->say_line_limit){
    $say = '';
    return false;
  }

  if($GAME_CONF->replace_talk) $say = strtr($say, $GAME_CONF->replace_talk_list); //発言置換モード

  //死者・ゲームプレイ中以外なら以降はスキップ
  if($SELF->IsDead() || ! $ROOM->IsPlaying()) return true;
  //if($SELF->IsDead()) return false; //テスト用

  //萌系置換 (昼限定)
  if($ROOM->IsDay() && $SELF->IsRole('suspect', 'cute_wolf', 'cute_fox') &&
     mt_rand(1, 100) <= $GAME_CONF->cute_wolf_rate){
    $say = $MESSAGE->cute_wolf != '' ? $MESSAGE->cute_wolf : $MESSAGE->wolf_howl;
  }

  $virtual_self = $USERS->ByVirtual($SELF->user_no);
  $ROLES->actor = $virtual_self;
  //紳士・淑女置換
  if($virtual_self->IsRole('gentleman', 'lady') &&
	 mt_rand(1, 100) <= $GAME_CONF->gentleman_rate){
    $role = $virtual_self->IsRole('gentleman') ? 'gentleman' : 'lady';

    $stack = $USERS->GetLivingUsers(); //生存者のユーザ名を取得
    unset($stack[array_search($virtual_self->uname, $stack)]); //自分を削除

    $say = $MESSAGE->{$role . '_header'} . $USERS->GetHandleName(GetRandom($stack), true) .
      $MESSAGE->{$role . '_footer'};
  }
  //狼少年変換
  elseif($virtual_self->IsRole('liar') && mt_rand(1, 100) <= $GAME_CONF->liar_rate){
    $say = strtr($say, $GAME_CONF->liar_replace_list);
  }

  if($virtual_self->IsRole('bad_status')){ //妖精の処理
    $stack = array('spring_fairy' => '春', 'summer_fairy' => '夏',
		   'autumn_fairy' => '秋', 'winter_fairy' => '冬');
    foreach($virtual_self->GetPartner('bad_status') as $id => $date){
      if($date != $ROOM->date) continue;
      $user = $USERS->ByID($id);
      if($user->IsRole('fairy')){
	$say = $MESSAGE->common_talk . $say;
      }
      elseif(array_key_exists($user->main_role, $stack)){
	$say = $stack[$user->main_role] . 'ですよー' . $say;
      }
    }
  }

  foreach($ROLES->Load('say') as $filter) $filter->FilterSay($say); //他のサブ役職の処理
  return true;
}

//遺言登録
function EntryLastWords($say){
  global $ROOM, $USERS, $SELF;

  if($ROOM->IsFinished()) return false; //ゲーム終了後ならスキップ

  if($say == ' ') $say = NULL; //スペースだけなら「消去」
  if($SELF->IsLive()){ //登録しない役職をチェック
    if(! $SELF->IsLastWordsLimited()) $SELF->Update('last_words', $say);
  }
  elseif($SELF->IsDead() && $SELF->IsRole('mind_evoke')){ //口寄せの処理
    foreach($SELF->GetPartner('mind_evoke') as $id){ //口寄せしているイタコすべての遺言を更新する
      $target = $USERS->ByID($id);
      if($target->IsLive()) $target->Update('last_words', $say);
    }
  }
}

//発言
function Say($say){
  global $RQ_ARGS, $ROOM, $USERS, $SELF;

  $user = $USERS->ByVirtual($SELF->user_no); //仮想ユーザを取得
  if($ROOM->IsRealTime()){ //リアルタイム制
    GetRealPassTime($left_time);
    $spend_time = 0; //会話で時間経過制の方は無効にする
  }
  else{ //会話で時間経過制
    GetTalkPassTime($left_time); //経過時間の和
    $spend_time = floor(strlen($say) / 100); //経過時間
    if($spend_time < 1) $spend_time = 1; //最小は 1
    elseif($spend_time > 4) $spend_time = 4; //最大は 4
  }

  if(! $ROOM->IsPlaying()){ //ゲーム開始前後はそのまま発言
    Write($say, $ROOM->day_night, 0, true);
  }
  //身代わり君 (仮想 GM 対応) は遺言を専用のシステムメッセージに切り替え
  elseif($SELF->IsDummyBoy() && $RQ_ARGS->last_words){
    Write($say, "{$ROOM->day_night} dummy_boy", 0); //発言時間を更新しない
  }
  elseif($SELF->IsDead()){ //死亡者の霊話
    Write($say, 'heaven', 0); //発言時間を更新しない
  }
  elseif($SELF->IsLive() && $left_time > 0){ //生存者で制限時間内
    if($ROOM->IsDay()){ //昼はそのまま発言
      if(! $ROOM->IsEvent('wait_morning')) Write($say, 'day', $spend_time, true);
    }
    elseif($ROOM->IsNight()){ //夜は役職毎に分ける
      $update = $SELF->IsWolf(); //時間経過するのは人狼の発言のみ (本人判定)
      if(! $update) $spend_time = 0;

      if($user->IsWolf(true)) //人狼
	$location = $SELF->IsRole('possessed_mad') ? 'self_talk' : 'wolf'; //犬神判定
      elseif($user->IsRole('whisper_mad')) //囁き狂人
	$location = 'mad';
      elseif($user->IsCommon(true)) //共有者
	$location = 'common';
      elseif($user->IsFox(true)) //妖狐
	$location = 'fox';
      else //独り言
	$location = 'self_talk';

      Write($say, 'night ' . $location, $spend_time, $update);
    }
  }
}

//発言を DB に登録する
function Write($say, $location, $spend_time, $update = false){
  global $RQ_ARGS, $ROOM, $ROLES, $USERS, $SELF;

  //声の大きさを決定
  $voice = $RQ_ARGS->font_type;
  if($ROOM->IsPlaying() && $SELF->IsLive()){
    $ROLES->actor = $USERS->ByVirtual($SELF->user_no);
    foreach($ROLES->Load('voice') as $filter) $filter->FilterVoice($voice, $say);
  }

  $ROOM->Talk($say, $SELF->uname, $location, $voice, $spend_time);
  if($update) $ROOM->UpdateTime();
  SendCommit();
}

//ゲーム停滞のチェック
function CheckSilence(){
  global $TIME_CONF, $MESSAGE, $ROOM, $USERS;

  if(! $ROOM->IsPlaying() || ! LockTable('game')) return false; //スキップ判定 + テーブルロック

  //最終発言時刻からの差分を取得
  $query = $ROOM->GetQueryHeader('room', 'UNIX_TIMESTAMP() - last_updated');
  $last_updated_pass_time = FetchResult($query);

  //経過時間を取得
  if($ROOM->IsRealTime()) //リアルタイム制
    GetRealPassTime($left_time);
  else //仮想時間制
    $silence_pass_time = GetTalkPassTime($left_time, true);

  if(! $ROOM->IsRealTime() && $left_time > 0){ //仮想時間制の沈黙判定
    if($last_updated_pass_time > $TIME_CONF->silence){
      $str = '・・・・・・・・・・ ' . $silence_pass_time . ' ' . $MESSAGE->silence;
      $ROOM->Talk($str, '', '', NULL, $TIME_CONF->silence_pass);
      $ROOM->UpdateTime();
    }
  }
  elseif($left_time == 0){ //制限時間超過時の処理
    //オープニングなら即座に夜に移行する
    if($ROOM->IsOption('open_day') && $ROOM->IsDay() && $ROOM->date == 1){
      //シーンを DB から再取得して切り替わっていなければ処理
      if(FetchResult($ROOM->GetQueryHeader('room', 'day_night')) == 'day'){
	$ROOM->ChangeNight(); //夜に切り替え
	$ROOM->UpdateTime(true); //最終書き込み時刻を更新
      }
      UnlockTable(); //テーブルロック解除
      return true;
    }

    //突然死発動までの時間を取得
    $sudden_death_announce = 'あと' . ConvertTime($TIME_CONF->sudden_death) . 'で' .
      $MESSAGE->sudden_death_announce;
    if($ROOM->OvertimeAlert($sudden_death_announce)){ //警告出力
      $ROOM->UpdateTime(); //更新時間を更新
      $last_updated_pass_time = 0;
    }
    else{ //一分刻みで追加の警告を出す
      $seconds = $TIME_CONF->sudden_death - $last_updated_pass_time;
      $quotient = $seconds % 60;
      $seconds -= $quotient;
      if($quotient > 0) $seconds += 60;
      if($seconds > $TIME_CONF->sudden_death) $seconds = $TIME_CONF->sudden_death;
      if($seconds > 0){
	$str = 'あと' . ConvertTime($seconds) . 'で' . $MESSAGE->sudden_death_announce;
	$ROOM->OvertimeAlert($str);
      }
    }
    $ROOM->sudden_death = $TIME_CONF->sudden_death - $last_updated_pass_time;

    //制限時間を過ぎていたら未投票の人を突然死させる
    if($ROOM->sudden_death <= 0){
      if(abs($ROOM->sudden_death) > $TIME_CONF->server_disconnect){ //サーバダウン検出
	$ROOM->UpdateTime(); //突然死タイマーをリセット
      }
      else{
	$ROOM->LoadVote(); //投票情報を取得
	if($ROOM->IsDay()){
	  //生存者と投票済みの人の差分を取る
	  $novote_uname_list = array_diff($USERS->GetLivingUsers(), array_keys($ROOM->vote));
	}
	elseif($ROOM->IsNight()){
	  $vote_data = $ROOM->ParseVote(); //投票情報をパース
	  //PrintData($vote_data, 'Vote Data');

	  $novote_uname_list = array();
	  foreach($USERS->rows as $user){ //未投票チェック
	    if($user->CheckVote($vote_data) === false) $novote_uname_list[] = $user->uname;
	  }
	}

	//未投票者を全員突然死させる
	foreach($novote_uname_list as $uname){
	  $USERS->SuddenDeath($USERS->ByUname($uname)->user_no, 'NOVOTED_' . $ROOM->day_night);
	}
	LoversFollowed(true);
	InsertMediumMessage();

	$ROOM->Talk($MESSAGE->vote_reset); //投票リセットメッセージ
	$ROOM->Talk($sudden_death_announce); //突然死告知メッセージ
	$ROOM->UpdateTime(); //制限時間リセット
	$ROOM->DeleteVote(); //投票リセット
	if(CheckVictory()) $USERS->ResetJoker(); //勝敗チェック
      }
    }
  }
  UnlockTable(); //テーブルロック解除
}

//超過時間セット
function SetSuddenDeathTime(){
  global $TIME_CONF, $ROOM;

  //最終発言時刻からの差分を取得
  $query = $ROOM->GetQueryHeader('room', 'UNIX_TIMESTAMP() - last_updated');
  $last_updated_pass_time = FetchResult($query);

  //経過時間を取得
  $ROOM->IsRealTime() ? GetRealPassTime($left_time) : GetTalkPassTime($left_time, true);
  if($left_time == 0) $ROOM->sudden_death = $TIME_CONF->sudden_death - $last_updated_pass_time;
}

//村名前、番地、何日目、日没まで～時間を出力(勝敗がついたら村の名前と番地、勝敗を出力)
function OutputGameHeader(){
  global $GAME_CONF, $TIME_CONF, $MESSAGE, $RQ_ARGS, $ROOM, $USERS, $SELF,
    $COOKIE, $SOUND, $OBJECTION;

  $url_room   = '?room_no=' . $ROOM->id;
  $url_reload = $RQ_ARGS->auto_reload > 0 ? '&auto_reload=' . $RQ_ARGS->auto_reload : '';
  $url_sound  = $RQ_ARGS->play_sound ? '&play_sound=on'  : '';
  $url_list   = $RQ_ARGS->list_down  ? '&list_down=on'   : '';
  $url_dead   = $ROOM->dead_mode     ? '&dead_mode=on'   : '';
  $url_heaven = $ROOM->heaven_mode   ? '&heaven_mode=on' : '';
  $real_time  = $ROOM->IsRealTime();

  echo '<table class="game-header"><tr>'."\n";
  if(($SELF->IsDead() && $ROOM->heaven_mode) || $ROOM->IsAfterGame()){ //霊界とログ閲覧時
    if($SELF->IsDead() && $ROOM->heaven_mode)
      echo '<td>&lt;&lt;&lt;幽霊の間&gt;&gt;&gt;</td>'."\n";
    else
      echo $ROOM->GenerateTitleTag();

    //過去の日のログへのリンク生成
    echo '<td class="view-option">ログ ';
    $url_header ='<a href="game_log.php' . $url_room . '&date=';
    $url_footer = '#game_top" target="_blank">';
    $url_day    = '&day_night=day'   . $url_footer;
    $url_night  = '&day_night=night' . $url_footer;

    echo $url_header . '0&day_night=beforegame' . $url_footer . '0(開始前)</a>'."\n";
    if($ROOM->IsOption('open_day')) echo $url_header . '1' . $url_day . '1(昼)</a>'."\n";
    echo $url_header . '1' . $url_night . '1(夜)</a>'."\n";
    for($i = 2; $i < $ROOM->date; $i++){
      echo $url_header . $i . $url_day   . $i . '(昼)</a>'."\n";
      echo $url_header . $i . $url_night . $i . '(夜)</a>'."\n";
    }
    if($ROOM->IsNight() && $ROOM->heaven_mode){
      echo $url_header . $ROOM->date . $url_day . $ROOM->date . '(昼)</a>'."\n";
    }
    elseif($ROOM->IsAfterGame()){
      if(FetchResult($ROOM->GetQuery(true, 'talk') . " AND location LIKE 'day%'") > 0){
	echo $url_header . $ROOM->date . $url_day . $ROOM->date . '(昼)</a>'."\n";
      }
    }

    if($ROOM->heaven_mode){
      echo '</td>'."\n" . '</tr></table>'."\n";
      return;
    }
  }
  else{
    echo $ROOM->GenerateTitleTag() . '<td class="view-option">'."\n";
    if($SELF->IsDead() && $ROOM->dead_mode){ //死亡者の場合の、真ん中の全表示地上モード
      $url = 'game_play.php' . $url_room . '&dead_mode=on' . $url_reload .
	$url_sound . $url_list;

      echo <<<EOF
<form method="POST" action="{$url}" name="reload_middle_frame" target="middle">
<input type="submit" value="更新">
</form>

EOF;
    }
  }

  if(! $ROOM->IsAfterGame()){ //ゲーム終了後は自動更新しない
    $url_header = '<a target="_top" href="game_frame.php' . $url_room .
      $url_dead . $url_heaven . $url_list;
    OutputAutoReloadLink($url_header . $url_sound  . '&auto_reload=');

    $url = $url_header . $url_reload . '&play_sound=';
    echo ' [音でお知らせ](' .
      ($RQ_ARGS->play_sound ?  'on ' . $url . 'off">off</a>' : $url . 'on">on</a> off') .
      ')'."\n";
  }

  //プレイヤーリストの表示位置
  echo '<a target="_top" href="game_frame.php' . $url_room . $url_dead . $url_heaven .
    $url_reload . $url_sound  . '&list_down=' . ($RQ_ARGS->list_down ? 'off">↑' : 'on">↓') .
    'リスト</a>'."\n";
  if($ROOM->IsFinished()) OutputLogLink();

  if($RQ_ARGS->play_sound && ($ROOM->IsBeforeGame() || $ROOM->IsDay())){ //音でお知らせ処理
    if($ROOM->IsBeforeGame()){ //入村・満員
      $user_count = $USERS->GetUserCount();
      $max_user   = FetchResult($ROOM->GetQueryHeader('room', 'max_user'));
      if($user_count == $max_user && $COOKIE->user_count != $max_user){
	$SOUND->Output('full');
      }
      elseif($COOKIE->user_count != $user_count){
	$SOUND->Output('entry');
      }
    }
    elseif($COOKIE->day_night != $ROOM->day_night){ //夜明け
      $SOUND->Output('morning');
    }

    //「異議」あり
    $cookie_objection_list = explode(',', $COOKIE->objection); //クッキーの値を配列に格納する
    $count = count($OBJECTION);
    for($i = 0; $i < $count; $i++){ //差分を計算 (index は 0 から)
      //差分があれば性別を確認して音を鳴らす
      if((int)$OBJECTION[$i] > (int)$cookie_objection_list[$i]){
	$SOUND->Output('objection_' . $USERS->ByID($i + 1)->sex);
      }
    }
  }
  echo '</td></tr>'."\n".'</table>'."\n";

  switch($ROOM->day_night){
  case 'beforegame': //開始前の注意を出力
    echo '<div class="caution">'."\n";
    echo 'ゲームを開始するには全員がゲーム開始に投票する必要があります';
    echo '<span>(投票した人は村人リストの背景が赤くなります)</span>'."\n";
    echo '</div>'."\n";
    break;

  case 'day':
    $time_message = '日没まで ';
    break;

  case 'night':
    $time_message = '夜明けまで ';
    break;

  case 'aftergame': //勝敗結果を出力して処理終了
    OutputVictory();
    return;
  }

  if($ROOM->IsBeforeGame()) OutputGameOption(); //ゲームオプションを説明

  OutputTimeTable(); //経過日数と生存人数を出力
  $left_time = 0;
  if($ROOM->IsBeforeGame()){
    echo '<td class="real-time">';
    if($real_time){ //実時間の制限時間を取得
      echo "設定時間： 昼 <span>{$ROOM->real_time->day}分</span> / " .
	"夜 <span>{$ROOM->real_time->night}分</span>";
    }
    echo '　突然死：<span>' . ConvertTime($TIME_CONF->sudden_death) . '</span></td>';
  }
  if($ROOM->IsPlaying()){
    if($real_time){ //リアルタイム制
      GetRealPassTime($left_time);
      echo '<td class="real-time"><form name="realtime_form">'."\n";
      echo '<input type="text" name="output_realtime" size="60" readonly>'."\n";
      echo '</form></td>'."\n";
    }
    else{ //仮想時間制
      echo '<td>' . $time_message . GetTalkPassTime($left_time) . '</td>'."\n";
    }
  }

  //異議あり、のボタン(夜と死者モード以外)
  if($ROOM->IsBeforeGame() ||
     ($ROOM->IsDay() && ! $ROOM->dead_mode && ! $ROOM->heaven_mode && $left_time > 0)){
    $url = 'game_play.php' . $url_room . $url_reload . $url_sound . $url_list;
    $count = $GAME_CONF->objection - $SELF->objection_count;
    echo <<<EOF
<td class="objection"><form method="POST" action="{$url}">
<input type="hidden" name="set_objection" value="on">
<input type="image" name="objimage" src="{$GAME_CONF->objection_image}" border="0">
</form></td>
<td>({$count})</td>

EOF;
  }
  echo '</tr></table>'."\n";

  if(! $ROOM->IsPlaying()) return;
  if($left_time == 0){
    echo '<div class="system-vote">' . $time_message . $MESSAGE->vote_announce . '</div>'."\n";
    if($ROOM->sudden_death > 0){
      echo $MESSAGE->sudden_death_time . ConvertTime($ROOM->sudden_death) . '<br>'."\n";
    }
  }
  elseif($ROOM->IsEvent('wait_morning')){
    echo '<div class="system-vote">' . $MESSAGE->wait_morning . '</div>'."\n";
  }

  if($SELF->IsDead() && $ROOM->IsOption('auto_open_cast') && ! $ROOM->IsOpenCast()){
    echo '<div class="system-vote">' . $MESSAGE->close_cast . '</div>'."\n";
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

//昼の自分の未投票チェック
function CheckSelfVoteDay(){
  global $MESSAGE, $ROOM, $USERS, $SELF;

  $vote_times = $ROOM->GetVoteTimes(); //投票回数を取得
  $str = '<div class="self-vote">投票 ' . $vote_times . ' 回目：';

  //投票対象者を取得
  $query = 'SELECT target_uname FROM vote' . $ROOM->GetQuery() .
    " AND situation = 'VOTE_KILL' AND vote_times = {$vote_times} AND uname = '{$SELF->uname}'";
  $target_uname = FetchResult($query);
  $str .= ($target_uname === false ? '<font color="#FF0000">まだ投票していません</font>' :
	   $USERS->GetHandleName($target_uname, true) . ' さんに投票済み') . '</div>'."\n";
  if($target_uname === false){
    $str .= '<span class="ability vote-do">' . $MESSAGE->ability_vote . '</span><br>'."\n";
  }
  echo $str;
}

//自分の遺言を出力
function OutputSelfLastWords(){
  global $ROOM, $SELF;

  if($ROOM->IsAfterGame()) return false; //ゲーム終了後は表示しない

  $query = 'SELECT last_words FROM user_entry' . $ROOM->GetQuery(false) .
    " AND uname = '{$SELF->uname}' AND user_no > 0";
  if(($str = FetchResult($query)) == '') return false;
  LineToBR($str); //改行コードを変換
  if($str == '') return false;

  echo <<<EOF
<table class="lastwords"><tr>
<td class="lastwords-title">自分の遺言</td>
<td class="lastwords-body">{$str}</td>
</tr></table>

EOF;
}
