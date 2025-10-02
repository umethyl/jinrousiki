<?php
require_once('include/init.php');
//$INIT_CONF->LoadFile('feedengine'); //RSS機能はテスト中
$INIT_CONF->LoadClass('ROOM_CONF', 'ROOM_IMG');

if(! $DB_CONF->Connect(true, false)) return false; //DB 接続
if (in_array('index_functions', $INIT_CONF->loaded->file)) MaintenanceRoom();
EncodePostData();
if(@$_POST['command'] == 'CREATE_ROOM'){
  $INIT_CONF->LoadClass('USER_ICON', 'MESSAGE', 'TWITTER');
  CreateRoom();
}
else{
  $INIT_CONF->LoadClass('CAST_CONF', 'TIME_CONF', 'GAME_OPT_CAPT');
  OutputRoomList();
}
$DB_CONF->Disconnect(); //DB 接続解除

//-- 関数 --//
//村のメンテナンス処理
function MaintenanceRoom(){
  global $SERVER_CONF, $ROOM_CONF;

  if($SERVER_CONF->disable_maintenance) return; //スキップ判定

  //一定時間更新の無い村は廃村にする
  $query = "UPDATE room SET status = 'finished', day_night = 'aftergame' " .
    "WHERE status <> 'finished' AND last_updated < UNIX_TIMESTAMP() - " . $ROOM_CONF->die_room;
  /*
  //RSS更新(廃村が0の時も必要ない処理なのでfalseに限定していない)
  if(SendQuery($query)) OutputSiteSummary();
  */
  SendQuery($query);

  //終了した部屋のセッションIDのデータをクリアする
  $query = <<<EOF
UPDATE room, user_entry SET user_entry.session_id = NULL
WHERE room.room_no = user_entry.room_no
AND room.status = 'finished' AND !(user_entry.session_id IS NULL)
AND (room.finish_time IS NULL OR
     room.finish_time < DATE_SUB(NOW(), INTERVAL {$ROOM_CONF->clear_session_id} SECOND))
EOF;
  SendQuery($query, true);
}

//村(room)の作成
function CreateRoom(){
  global $SERVER_CONF, $ROOM_CONF, $USER_ICON, $TWITTER;

  if($SERVER_CONF->disable_establish) OutputActionResult('村作成 [制限事項]', '村作成はできません');
  if(CheckReferer('', array('127.0.0.1', '192.168.'))){ //リファラチェック
    OutputActionResult('村作成 [入力エラー]', '無効なアクセスです。');
  }

  //-- 入力データのエラーチェック --//
  //村の名前・説明のデータチェック
  foreach(array('room_name' => '村の名前', 'room_comment' => '村の説明') as $str => $name){
    $$str = @$_POST[$str];
    EscapeStrings($$str);
    if($$str == ''){ //未入力チェック
      OutputRoomAction('empty', $name);
      return false;
    }
    if(strlen($$str) > $ROOM_CONF->$str || preg_match($ROOM_CONF->ng_word, $$str)){ //文字列チェック
      OutputRoomAction('comment', $name);
      return false;
    }
  }

  //最大人数チェック
  $max_user = @(int)$_POST['max_user'];
  if(! in_array($max_user, $ROOM_CONF->max_user_list)){
    OutputActionResult('村作成 [入力エラー]', '無効な最大人数です。');
  }

  $ip_address = @$_SERVER['REMOTE_ADDR']; //処理実行ユーザの IP を取得
  if(! $SERVER_CONF->debug_mode){ //デバッグモード時は村作成制限をスキップ
    $str = 'room_password'; //パスワードチェック
    if(isset($SERVER_CONF->$str) && @$_POST[$str] != $SERVER_CONF->$str){
      OutputActionResult('村作成 [制限事項]', '村作成パスワードが正しくありません。');
    }

    //ブラックリストチェック
    if(CheckBlackList()) OutputActionResult('村作成 [制限事項]', '村立て制限ホストです。');

    $query = "FROM room WHERE status <> 'finished'"; //チェック用の共通クエリ
    $time  = FetchResult("SELECT MAX(establish_time) {$query}"); //連続作成制限チェック
    if(isset($time) && TZTime() - ConvertTimeStamp($time, false) <= $ROOM_CONF->establish_wait){
      OutputRoomAction('establish_wait');
      return false;
    }

    //最大稼働数チェック
    if(FetchResult("SELECT COUNT(room_no) {$query}") >= $ROOM_CONF->max_active_room){
      OutputRoomAction('full');
      return false;
    }

    //同一ユーザの連続作成チェック (終了していなければエラー処理)
    if(FetchResult("SELECT COUNT(room_no) {$query} AND establisher_ip = '{$ip_address}'") > 0){
      OutputRoomAction('over_establish');
      return false;
    }
  }

  //-- ゲームオプションをセット --//
  $perverseness = $ROOM_CONF->perverseness && @$_POST['perverseness']  == 'on';
  $full_mania   = $ROOM_CONF->full_mania   && @$_POST['replace_human'] == 'full_mania';
  $full_cupid   = $ROOM_CONF->full_cupid   && @$_POST['replace_human'] == 'full_cupid';
  $chaos        = $ROOM_CONF->chaos        && @$_POST['special_role']  == 'chaos';
  $chaosfull    = $ROOM_CONF->chaosfull    && @$_POST['special_role']  == 'chaosfull';
  $chaos_hyper  = $ROOM_CONF->chaos_hyper  && @$_POST['special_role']  == 'chaos_hyper';
  $chaos_verso  = $ROOM_CONF->chaos_verso  && @$_POST['special_role']  == 'chaos_verso';
  $quiz         = $ROOM_CONF->quiz         && @$_POST['special_role']  == 'quiz';
  $special_role =
    ($ROOM_CONF->duel         && @$_POST['special_role']  == 'duel') ||
    ($ROOM_CONF->gray_random  && @$_POST['special_role']  == 'gray_random');
  $game_option_list = array();
  $option_role_list = array();
  $check_game_option_list = array('wish_role', 'open_vote', 'seal_message', 'open_day',
				  'not_open_cast');
  $check_option_role_list = array();
  if($quiz){ //クイズ村
    $gm_password = @$_POST['gm_password']; //GM ログインパスワードをチェック
    EscapeStrings($gm_password);
    if($gm_password == ''){
      OutputRoomAction('no_password');
      return false;
    }
    array_push($game_option_list, 'dummy_boy', 'quiz');
    $dummy_boy_handle_name = 'GM';
    $dummy_boy_password    = $gm_password;
  }
  else{
    //身代わり君関連のチェック
    if($ROOM_CONF->dummy_boy && @$_POST['dummy_boy'] == 'on'){
      $game_option_list[]       = 'dummy_boy';
      $dummy_boy_handle_name    = '身代わり君';
      $dummy_boy_password       = $SERVER_CONF->system_password;
      $check_option_role_list[] = 'gerd';
    }
    elseif($ROOM_CONF->dummy_boy && @$_POST['dummy_boy'] == 'gm_login'){
      $gm_password = @$_POST['gm_password']; //GM ログインパスワードをチェック
      EscapeStrings($gm_password);
      if($gm_password == ''){
	OutputRoomAction('no_password');
	return false;
      }
      array_push($game_option_list, 'dummy_boy', 'gm_login');
      $dummy_boy_handle_name    = 'GM';
      $dummy_boy_password       = $gm_password;
      $check_option_role_list[] = 'gerd';
    }

    if($chaos || $chaosfull || $chaos_hyper || $chaos_verso){ //闇鍋モード
      $game_option_list[] = @$_POST['special_role'];
      $check_game_option_list[] = 'secret_sub_role';
      array_push($check_option_role_list, 'topping', 'boost_rate', 'chaos_open_cast',
		 'sub_role_limit');
    }
    elseif($special_role){ //特殊配役モード
      $option_role_list[] = @$_POST['special_role'];
    }
    else{ //通常村
      array_push($check_option_role_list, 'poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf',
		 'possessed_wolf', 'sirius_wolf', 'fox', 'child_fox');
      if(! $full_cupid) $check_option_role_list[] = 'cupid';
      $check_option_role_list[] = 'medium';
      if(! $full_mania) $check_option_role_list[] = 'mania';
      if(! $perverseness) array_push($check_option_role_list, 'decide', 'authority');
    }
    array_push($check_game_option_list, 'deep_sleep', 'blinder', 'mind_open', 'joker',
	       'death_note', 'weather', 'festival');
    if(! $special_role) $check_option_role_list[] = 'detective';
    array_push($check_option_role_list, 'liar', 'gentleman', 'critical',
	       $perverseness ? 'perverseness' : 'sudden_death', 'replace_human', 'change_common',
	       'change_mad', 'change_cupid');
  }

  //PrintData($_POST, 'Post');
  //PrintData($check_game_option_list, 'CheckGameOption');
  foreach($check_game_option_list as $option){
    if(! $ROOM_CONF->$option) continue;

    switch($option){
    case 'not_open_cast':
      switch($target = @$_POST[$option]){
      case 'not':
      case 'auto':
	$option = $target . '_open_cast';
	if($ROOM_CONF->$option) break 2;
      }
      continue 2;

    default:
      if(@$_POST[$option] != 'on') continue 2;
    }
    $game_option_list[] = $option;
  }
  //PrintData($game_option_list);


  //PrintData($check_option_role_list, 'CheckOptionRole');
  foreach($check_option_role_list as $option){
    if(! $ROOM_CONF->$option) continue;

    switch($option){
    case 'replace_human':
    case 'change_common':
    case 'change_mad':
    case 'change_cupid':
      $target = @$_POST[$option];
      if(empty($target) || ! $ROOM_CONF->$target ||
	 ! in_array($target, $ROOM_CONF->{$option.'_list'})) continue 2;
      $option = $target;
      break;

    case 'topping':
    case 'boost_rate':
      $target = @$_POST[$option];
      if(array_search($target, $ROOM_CONF->{$option.'_list'}) === false) continue 2;
      $option .= ':' . $target;
      break;

    case 'chaos_open_cast':
      switch($target = @$_POST[$option]){
      case 'full':
	break 2;

      case 'camp':
      case 'role':
	$option .= '_' . $target;
	if($ROOM_CONF->$option) break 2;
      }
      continue 2;

    case 'sub_role_limit':
      switch($target = @$_POST[$option]){
      case 'no_sub_role':
      case 'sub_role_limit_easy':
      case 'sub_role_limit_normal':
      case 'sub_role_limit_hard':
	if($ROOM_CONF->$target){
	  $option = $target;
	  break 2;
	}
      }
      continue 2;

    default:
      if(@$_POST[$option] != 'on') continue 2;
    }
    $option_role_list[] = $option;
  }

  if($ROOM_CONF->real_time && @$_POST['real_time'] == 'on'){
    $day   = @$_POST['real_time_day'];
    $night = @$_POST['real_time_night'];

    //制限時間チェック
    if($day   != '' && ! preg_match('/[^0-9]/', $day)   && $day   > 0 && $day   < 99 &&
       $night != '' && ! preg_match('/[^0-9]/', $night) && $night > 0 && $night < 99){
      $game_option_list[] = 'real_time:' . $day . ':' . $night;
    }
    else{
      OutputRoomAction('time');
      return false;
    }

    $option = 'wait_morning';
    if($ROOM_CONF->$option && @$_POST[$option] == 'on') $game_option_list[] = $option . ':';
  }

  //PrintData($game_option_list, 'GameOption');
  //PrintData($option_role_list, 'OptionRole');
  //OutputHTMLFooter(true);

  //テーブルをロック
  if(LockTable()){
    OutputRoomAction('busy');
    return false;
  }

  //登録
  $room_no     = FetchResult('SELECT MAX(room_no) FROM room') + 1; //村番号の最大値を取得
  $game_option = implode(' ', $game_option_list);
  $option_role = implode(' ', $option_role_list);
  $status      = false;
  do{
    if(! $SERVER_CONF->dry_run_mode){
      //村作成
      $time   = TZTime();
      $items  = 'room_no, room_name, room_comment, establisher_ip, establish_time, ' .
	'game_option, option_role, max_user, status, date, day_night, last_updated';
      $values = "{$room_no}, '{$room_name}', '{$room_comment}', '{$ip_address}', NOW(), " .
	"'{$game_option}', '{$option_role}', {$max_user}, 'waiting', 0, 'beforegame', '{$time}'";
      if(! InsertDatabase('room', $items, $values)) break;

      //身代わり君を入村させる
      if(in_array('dummy_boy', $game_option_list) &&
	 FetchResult('SELECT COUNT(uname) FROM user_entry WHERE room_no = ' . $room_no) == 0){
	if(! InsertUser($room_no, 'dummy_boy', $dummy_boy_handle_name, $dummy_boy_password,
			1, in_array('gerd', $option_role_list) ? $USER_ICON->gerd : 0)) break;
      }

      if($SERVER_CONF->secret_room){ //村情報非表示モードの処理
	OutputRoomAction('success', $room_name);
	return true;
      }
    }

    $TWITTER->Send($room_no, $room_name, $room_comment); //Twitter 投稿処理
    //OutputSiteSummary(); //RSS更新 //テスト中

    OutputRoomAction('success', $room_name);
    $status = true;
  }while(false);
  if(! $status) OutputRoomAction('busy');
  return true;
}

//結果出力 (CreateRoom() 用)
function OutputRoomAction($type, $str = ''){
  global $SERVER_CONF;

  switch($type){
  case 'empty':
    OutputActionResultHeader('村作成 [入力エラー]');
    echo 'エラーが発生しました。<br>';
    echo '以下の項目を再度ご確認ください。<br>';
    echo "<ul><li>{$str}が記入されていない。</li>";
    break;

  case 'comment':
    OutputActionResultHeader('村作成 [入力エラー]');
    echo 'エラーが発生しました。<br>';
    echo '以下の項目を再度ご確認ください。<br>';
    echo "<ul><li>{$str}の文字数が長すぎる。</li>";
    echo "<li>{$str}に入力禁止文字列が含まれている。</li></ul>";
    break;

  case 'establish_wait':
    OutputActionResultHeader('村作成 [制限事項]');
    echo 'サーバで設定されている村立て許可時間間隔を経過していません。<br>'."\n";
    echo 'しばらく時間を開けてから再度登録してください。';
    break;

  case 'full':
    OutputActionResultHeader('村作成 [制限事項]');
    echo '現在プレイ中の村の数がこのサーバで設定されている最大値を超えています。<br>'."\n";
    echo 'どこかの村で決着がつくのを待ってから再度登録してください。';
    break;

  case 'over_establish':
    OutputActionResultHeader('村作成 [制限事項]');
    echo 'あなたが立てた村が現在稼働中です。<br>'."\n";
    echo '立てた村の決着がつくのを待ってから再度登録してください。';
    break;

  case 'no_password':
    OutputActionResultHeader('村作成 [入力エラー]');
    echo '有効な GM ログインパスワードが設定されていません。';
    break;

  case 'time':
    OutputActionResultHeader('村作成 [入力エラー]');
    echo 'エラーが発生しました。<br>';
    echo '以下の項目を再度ご確認ください。<br>';
    echo '<ul><li>リアルタイム制の昼・夜の時間を記入していない。</li>';
    echo '<li>リアルタイム制の昼・夜の時間が 0 以下、または 99 以上である。</li>';
    echo '<li>リアルタイム制の昼・夜の時間を全角で入力している。</li>';
    echo '<li>リアルタイム制の昼・夜の時間が数字ではない。</li></ul>';
    break;

  case 'busy':
    OutputActionResultHeader('村作成 [データベースエラー]');
    echo 'データベースサーバが混雑しています。<br>'."\n";
    echo '時間を置いて再度登録してください。';
    break;

  case 'success':
    OutputActionResultHeader('村作成', $SERVER_CONF->site_root);
    echo $str . ' 村を作成しました。トップページに飛びます。';
    echo '切り替わらないなら <a href="' . $SERVER_CONF->site_root . '">ここ</a> 。';
    break;
  }
  OutputHTMLFooter(); //フッタ出力
}

//村(room)のwaitingとplayingのリストを出力する
function OutputRoomList(){
  global $SERVER_CONF, $ROOM_IMG;

  if($SERVER_CONF->secret_room) return; //シークレットテストモード

  /* RSS機能はテスト中
  if(! $SERVER_CONF->debug_mode){
    $filename = JINRO_ROOT.'/rss/rooms.rss';
    if(file_exists($filename)){
      $rss = FeedEngine::Initialize('site_summary.php');
      $rss->Import($filename);
    }
    else{
      $rss = OutputSiteSummary();
    }
    foreach($rss->items as $item){
      extract($item, EXTR_PREFIX_ALL, 'room');
      echo $room_description;
    }
  }
  */

  //部屋情報を取得
  $delete_header = '<a href="admin/room_delete.php?room_no=';
  $delete_footer = '">[削除 (緊急用)]</a>'."\n";
  $query = 'SELECT room_no, room_name, room_comment, game_option, option_role, max_user, status ' .
    "FROM room WHERE status <> 'finished' ORDER BY room_no DESC";
  foreach(FetchAssoc($query) as $stack){
    extract($stack);
    $delete     = $SERVER_CONF->debug_mode ? $delete_header . $room_no . $delete_footer : '';
    $status_img = $ROOM_IMG->Generate($status, $status == 'waiting' ? '募集中' : 'プレイ中');
    $option_img = GenerateGameOptionImage($game_option, $option_role) .
      GenerateMaxUserImage($max_user);
    echo <<<EOF
{$delete}<a href="login.php?room_no={$room_no}">
{$status_img}<span>[{$room_no}番地]</span>{$room_name}村<br>
<div>～{$room_comment}～ {$option_img}</div>
</a><br>

EOF;
  }
}

//部屋作成画面を出力
function OutputCreateRoomPage(){
  global $SERVER_CONF, $ROOM_CONF, $GAME_OPT_MESS, $GAME_OPT_CAPT;

  if($SERVER_CONF->disable_establish){
    echo '村作成はできません';
    return;
  }

  echo <<<EOF
<form method="POST" action="room_manager.php">
<input type="hidden" name="command" value="CREATE_ROOM">
<table>

EOF;

  OutputRoomOption(array('room_name', 'room_comment', 'max_user'), '', false);
  OutputRoomOption(array('wish_role', 'real_time', 'wait_morning', 'open_vote', 'seal_message',
			 'open_day'));
  OutputRoomOptionDummyBoy();
  OutputRoomOptionOpenCast();

  $stack = array('poison', 'assassin', 'wolf', 'boss_wolf', 'poison_wolf', 'possessed_wolf',
		 'sirius_wolf', 'fox', 'child_fox', 'cupid', 'medium', 'mania',
		 'decide', 'authority');
  OutputRoomOption($stack, 'role');

  $stack = array('detective', 'liar', 'gentleman', 'deep_sleep', 'blinder', 'mind_open',
		 'critical', 'sudden_death', 'perverseness',  'joker', 'death_note', 'weather',
		 'festival', 'replace_human', 'change_common', 'change_mad', 'change_cupid');
  OutputRoomOption($stack, 'role');

  OutputRoomOption(array('special_role'));
  OutputRoomOptionChaos();

  $password = is_null($SERVER_CONF->room_password) ? '' :
    '<label for="room_password">村作成パスワード</label>：' .
    '<input type="password" id="room_password" name="room_password" size="20">　';
  echo <<<EOF
<tr><td colspan="2"><hr></td></tr>
<tr><td class="make" colspan="2">{$password}<input type="submit" value=" 作成 "></td></tr>
</table>
</form>

EOF;
}

//村作成フォーム生成 (チェックボックス型)
function GenerateRoomOption($option, $label = ''){
  global $ROOM_CONF, $TIME_CONF, $CAST_CONF, $GAME_OPT_MESS, $GAME_OPT_CAPT;

  if(property_exists($ROOM_CONF, $option) && $ROOM_CONF->$option === false) return NULL;

  $caption = property_exists($GAME_OPT_CAPT, $option) ? $GAME_OPT_CAPT->$option : NULL;
  switch($option){
  case 'room_name':
  case 'room_comment':
  case 'gm_password':
    return GenerateTextForm($option);

  case 'max_user':
  case 'replace_human':
  case 'change_common':
  case 'change_mad':
  case 'change_cupid':
  case 'special_role':
  case 'topping':
  case 'boost_rate':
    return GenerateSelector($option);

  case 'real_time':
    $caption .= <<<EOF
　昼：<input type="text" name="real_time_day" value="{$TIME_CONF->default_day}" size="2" maxlength="2">分 夜：<input type="text" name="real_time_night" value="{$TIME_CONF->default_night}" size="2" maxlength="2">分
EOF;
    break;
  }

  if($label != '') $label .= '_';
  $label .= $option;
  $str = $GAME_OPT_MESS->$option;
  if(property_exists($CAST_CONF, $option) && is_int($limit = $CAST_CONF->$option)){
    $str .= ' ('  . $limit . '人～)';
  }
  $checked = $ROOM_CONF->{'default_'.$option} ? ' checked' : '';

  return <<<EOF
<tr>
<td><label for="{$label}">{$str}：</label></td>
<td class="explain">
<input type="checkbox" id="{$label}" name="{$option}" value="on"{$checked}>
({$caption})
</td>
</tr>

EOF;
}

//村作成フォーム生成 (テキスト型)
function GenerateTextForm($option){
  global $ROOM_CONF, $GAME_OPT_MESS, $GAME_OPT_CAPT;

  $type   = 'text';
  $footer = '';
  switch($option){
  case 'room_name':
    $footer = ' 村';
    break;

  case 'gm_password':
    $type   = 'password';
    $footer = '<span class="explain">' . $GAME_OPT_CAPT->$option . '</span>';
    break;
  }
  $size = $ROOM_CONF->{$option.'_input'};

  return <<<EOF
<tr>
<td><label for="{$option}">{$GAME_OPT_MESS->$option}：</label></td>
<td><input type="{$type}" id="{$option}" name="{$option}" size="{$size}" value="">{$footer}</td>
</tr>

EOF;
}

//村作成フォーム生成 (セレクタ型)
function GenerateSelector($option){
  global $ROOM_CONF, $GAME_OPT_MESS, $GAME_OPT_CAPT;

  switch($option){
  case 'max_user':
    $label = '最大人数';
    $str   = '';
    foreach($ROOM_CONF->{$option.'_list'} as $number){
      $str .= '<option value="' . $number . '"' .
	($number == $ROOM_CONF->default_max_user ? ' selected' : '') . '>' .
	$number . '</option>'."\n";
    }
    break;

  case 'replace_human':
  case 'change_common':
  case 'change_mad':
  case 'change_cupid':
  case 'special_role':
    $label = 'モード名';
    $str   = '<option value="" selected>なし</option>'."\n";
    foreach($ROOM_CONF->{$option.'_list'} as $role){
      if($ROOM_CONF->$role){
	$str .= '<option value="' . $role . '">' . $GAME_OPT_MESS->$role . '</option>'."\n";
      }
    }
    break;

  case 'topping':
  case 'boost_rate':
    $label = 'タイプ名';
    $str   = '<option value="" selected>なし</option>'."\n";
    foreach($ROOM_CONF->{$option.'_list'} as $mode){
      $role = $option . '_' . $mode;
      if($GAME_OPT_MESS->$role){
	$str .= '<option value="' . $mode . '">' . $GAME_OPT_MESS->$role . '</option>'."\n";
      }
    }
    break;
  }

  return <<<EOF
<tr>
<td><label for="{$option}">{$GAME_OPT_MESS->$option}：</label></td>
<td>
<select id="{$option}" name="{$option}">
<optgroup label="{$label}">
{$str}</optgroup>
</select>
<span class="explain">({$GAME_OPT_CAPT->$option})</span></td>
</tr>

EOF;
}

//村作成フォーム出力 (標準)
function OutputRoomOption($option_list, $label = '', $border = true){
  $stack = array();
  foreach($option_list as $option) $stack[] = GenerateRoomOption($option, $label);
  if(count($stack) < 1) return NULL;
  if($border) array_unshift($stack, '<tr><td colspan="2"><hr></td></tr>');
  echo implode('', $stack);
}

//村作成フォーム出力 (身代わり君用)
function OutputRoomOptionDummyBoy(){
  global $ROOM_CONF, $GAME_OPT_MESS, $GAME_OPT_CAPT;

  if(! $ROOM_CONF->dummy_boy) return NULL;

  $checked_dummy_boy = '';
  $checked_gm_login  = '';
  $checked_nothing   = '';
  if($ROOM_CONF->default_dummy_boy)
    $checked_dummy_boy = ' id="dummy_boy" checked';
  elseif($ROOM_CONF->default_gm_login)
    $checked_gm_login  = ' id="dummy_boy" checked';
  else
    $checked_nothing   = ' id="dummy_boy" checked';

  echo <<<EOF
<tr><td colspan="2"><hr></td></tr>
<tr>
<td><label for="dummy_boy">{$GAME_OPT_MESS->dummy_boy}：</label></td>
<td class="explain">
<input type="radio" name="dummy_boy" value=""{$checked_nothing}>
{$GAME_OPT_CAPT->no_dummy_boy}<br>
<input type="radio" name="dummy_boy" value="on"{$checked_dummy_boy}>
{$GAME_OPT_CAPT->dummy_boy}<br>
<input type="radio" name="dummy_boy" value="gm_login"{$checked_gm_login}>
{$GAME_OPT_MESS->gm_login} ({$GAME_OPT_CAPT->gm_login})
</td>
</tr>

EOF;

  OutputRoomOption(array('gm_password', 'gerd'), '', false);
}

//村作成フォーム出力 (霊界配役用)
function OutputRoomOptionOpenCast(){
  global $ROOM_CONF, $GAME_OPT_MESS, $GAME_OPT_CAPT;

  if(! $ROOM_CONF->not_open_cast) return NULL;

  $checked_close = '';
  $checked_auto  = '';
  $checked_open  = '';
  switch($ROOM_CONF->default_not_open_cast){
  case 'full':
    $checked_close = ' id="not_open_cast" checked';
    break;

  case 'auto':
    if($ROOM_CONF->auto_open_cast){
      $checked_auto = ' id="not_open_cast" checked';
      break;
    }

  default:
    $checked_open = ' id="not_open_cast" checked';
    break;
  }

  echo <<<EOF
<tr><td colspan="2"><hr></td></tr>
<tr>
<td><label for="not_open_cast">{$GAME_OPT_MESS->not_open_cast}：</label></td>
<td class="explain">
<input type="radio" name="not_open_cast" value=""{$checked_open}>
{$GAME_OPT_CAPT->no_close_cast}<br>

<input type="radio" name="not_open_cast" value="not"{$checked_close}>
{$GAME_OPT_CAPT->not_open_cast}<br>

EOF;

  if($ROOM_CONF->auto_open_cast){
    echo <<<EOF
<input type="radio" name="not_open_cast" value="auto"{$checked_auto}>
{$GAME_OPT_CAPT->auto_open_cast}
</td>
</tr>

EOF;
  }
}

//村作成フォーム出力 (闇鍋モード用)
function OutputRoomOptionChaos(){
  global $ROOM_CONF, $GAME_OPT_MESS, $GAME_OPT_CAPT;

  if(! $ROOM_CONF->chaos) return NULL;

  OutputRoomOption(array('topping', 'boost_rate'));
  if($ROOM_CONF->chaos_open_cast){
    $checked_chaos_open_cast_full = '';
    $checked_chaos_open_cast_camp = '';
    $checked_chaos_open_cast_role = '';
    $checked_chaos_open_cast_none = '';
    switch($ROOM_CONF->default_chaos_open_cast){
    case 'full':
      $checked_chaos_open_cast_full = ' id="chaos_open_cast" checked';
      break;

    case 'camp':
      if($ROOM_CONF->chaos_open_cast_camp)
	$checked_chaos_open_cast_camp = ' id="chaos_open_cast" checked';
      else
	$checked_chaos_open_cast_none = ' id="chaos_open_cast" checked';
      break;

    case 'role':
      if($ROOM_CONF->chaos_open_cast_role)
	$checked_chaos_open_cast_role = ' id="chaos_open_cast" checked';
      else
	$checked_chaos_open_cast_none = ' id="chaos_open_cast" checked';
      break;

    default:
      $checked_chaos_open_cast_none = ' id="chaos_open_cast" checked';
      break;
    }

    $str = <<<EOF
<tr>
<td><label for="chaos_open_cast">{$GAME_OPT_MESS->chaos_open_cast}：</label></td>
<td class="explain">
<input type="radio" name="chaos_open_cast" value=""{$checked_chaos_open_cast_none}>
{$GAME_OPT_CAPT->chaos_not_open_cast}<br>

EOF;

  if($ROOM_CONF->chaos_open_cast_camp){
    $str .= <<<EOF
<input type="radio" name="chaos_open_cast" value="camp"{$checked_chaos_open_cast_camp}>
{$GAME_OPT_CAPT->chaos_open_cast_camp}<br>

EOF;
  }
  if($ROOM_CONF->chaos_open_cast_role){
    $str .= <<<EOF
<input type="radio" name="chaos_open_cast" value="role"{$checked_chaos_open_cast_role}>
{$GAME_OPT_CAPT->chaos_open_cast_role}<br>

EOF;
  }

echo $str .= <<<EOF
<input type="radio" name="chaos_open_cast" value="full"{$checked_chaos_open_cast_full}>
{$GAME_OPT_CAPT->chaos_open_cast_full}
</td>
</tr>

EOF;
  }

  if($ROOM_CONF->sub_role_limit){
    $checked_no_sub_role           = '';
    $checked_sub_role_limit_none   = '';
    $checked_sub_role_limit_easy   = '';
    $checked_sub_role_limit_normal = '';
    $checked_sub_role_limit_hard   = '';
    switch($ROOM_CONF->default_sub_role_limit){
    case 'no':
      if($ROOM_CONF->no_sub_role)
	$checked_no_sub_role = ' id="sub_role_limit" checked';
      else
	$checked_sub_role_limit_none = ' id="sub_role_limit" checked';
      break;

    case 'easy':
      if($ROOM_CONF->sub_role_limit_easy)
	$checked_sub_role_limit_easy = ' id="sub_role_limit" checked';
      else
	$checked_sub_role_limit_none = ' id="sub_role_limit" checked';
      break;

    case 'normal':
      if($ROOM_CONF->sub_role_limit_normal)
	$checked_sub_role_limit_normal = ' id="sub_role_limit" checked';
      else
	$checked_sub_role_limit_none = ' id="sub_role_limit" checked';
      break;

    case 'hard':
      if($ROOM_CONF->sub_role_limit_hard)
	$checked_sub_role_limit_hard = ' id="sub_role_limit" checked';
      else
	$checked_sub_role_limit_none = ' id="sub_role_limit" checked';
      break;

    default:
      $checked_sub_role_limit_none = ' id="sub_role_limit" checked';
      break;
    }

    $str = <<<EOF
<tr>
<td><label for="sub_role_limit">{$GAME_OPT_MESS->sub_role_limit}：</label></td>
<td class="explain">

EOF;

    if($ROOM_CONF->no_sub_role){
      $str .= <<<EOF
<input type="radio" name="sub_role_limit" value="no_sub_role"{$checked_no_sub_role}>
{$GAME_OPT_CAPT->no_sub_role}<br>

EOF;
    }
    if($ROOM_CONF->sub_role_limit_easy){
      $str .= <<<EOF
<input type="radio" name="sub_role_limit" value="sub_role_limit_easy"{$checked_sub_role_limit_easy}>
{$GAME_OPT_CAPT->sub_role_limit_easy}<br>

EOF;
    }
    if($ROOM_CONF->sub_role_limit_normal){
      $str .= <<<EOF
<input type="radio" name="sub_role_limit" value="sub_role_limit_normal"{$checked_sub_role_limit_normal}>
{$GAME_OPT_CAPT->sub_role_limit_normal}<br>

EOF;
    }
    if($ROOM_CONF->sub_role_limit_hard){
      $str .= <<<EOF
<input type="radio" name="sub_role_limit" value="sub_role_limit_hard"{$checked_sub_role_limit_hard}>
{$GAME_OPT_CAPT->sub_role_limit_hard}<br>

EOF;
    }

    echo $str .= <<<EOF
<input type="radio" name="sub_role_limit" value=""{$checked_sub_role_limit_none}>
{$GAME_OPT_CAPT->sub_role_limit_none}<br>
</td>
</tr>

EOF;
  }
  OutputRoomOption(array('secret_sub_role'), '', false);
}
