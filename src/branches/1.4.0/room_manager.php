<?php
require_once('include/init.php');
//$INIT_CONF->LoadFile('feedengine'); //RSS機能はテスト中
$INIT_CONF->LoadClass('ROOM_CONF', 'CAST_CONF', 'TIME_CONF', 'USER_ICON', 'ROOM_IMG',
		      'MESSAGE', 'GAME_OPT_CAPT');

if(! $DB_CONF->Connect(true, false)) return false; //DB 接続
if (in_array('version', $INIT_CONF->loaded->file)) MaintenanceRoom();
EncodePostData();
if(array_key_exists('command', $_POST) && $_POST['command'] == 'CREATE_ROOM')
  CreateRoom();
else
  OutputRoomList();
$DB_CONF->Disconnect(); //DB 接続解除

//-- 関数 --//
//村のメンテナンス処理
function MaintenanceRoom(){
  global $ROOM_CONF;

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
  global $DEBUG_MODE, $SERVER_CONF, $ROOM_CONF, $USER_ICON, $MESSAGE;

  if($SERVER_CONF->disable_establish) OutputActionResult('村作成 [制限事項]', '村作成はできません');
  if(CheckReferer('', array('127.0.0.1', '192.168.'))){ //リファラチェック
    OutputActionResult('村作成 [入力エラー]', '無効なアクセスです。');
  }

  //入力データのエラーチェック
  $room_name    = $_POST['room_name'];
  $room_comment = $_POST['room_comment'];
  EscapeStrings($room_name);
  EscapeStrings($room_comment);
  if($room_name == '' || $room_comment == ''){ //未入力チェック
    OutputRoomAction('empty');
    return false;
  }

  //文字列チェック
  if(strlen($room_name)    > $ROOM_CONF->room_name ||
     strlen($room_comment) > $ROOM_CONF->room_comment ||
     preg_match($ROOM_CONF->ng_word, $room_name) ||
     preg_match($ROOM_CONF->ng_word, $room_comment)){
    OutputRoomAction('comment');
    return false;
  }

  //指定された人数の配役があるかチェック
  $max_user = (int)$_POST['max_user'];
  if(! in_array($max_user, $ROOM_CONF->max_user_list)){
    OutputActionResult('村作成 [入力エラー]', '無効な最大人数です。');
  }

  $query = "FROM room WHERE status <> 'finished'"; //チェック用の共通クエリ
  $ip_address = $_SERVER['REMOTE_ADDR']; //村立てを行ったユーザの IP を取得

  //デバッグモード時は村立て制限をしない
  if(! $DEBUG_MODE){
    if(isset($SERVER_CONF->room_password) &&
       $SERVER_CONF->room_password != $_POST['room_password']){ //パスワードチェック
      OutputRoomAction('room_password');
      return false;
    }

    if(CheckBlackList()){ //ブラックリストチェック
      OutputRoomAction('black_list');
      return false;
    }

    //同じユーザが立てた村が終了していなければ新しい村を作らない
    if(FetchResult("SELECT COUNT(room_no) {$query} AND establisher_ip = '{$ip_address}'") > 0){
      OutputRoomAction('over_establish');
      return false;
    }

    //最大並列村数を超えているようであれば新しい村を作らない
    if(FetchResult('SELECT COUNT(room_no)' . $query) >= $ROOM_CONF->max_active_room){
      OutputRoomAction('full');
      return false;
    }

    //連続村立て制限チェック
    $time_stamp = FetchResult("SELECT establish_time {$query} ORDER BY room_no DESC");
    if(isset($time_stamp) &&
       TZTime() - ConvertTimeStamp($time_stamp, false) <= $ROOM_CONF->establish_wait){
      OutputRoomAction('establish_wait');
      return false;
    }
  }

  //ゲームオプションをセット
  $perverseness = $ROOM_CONF->perverseness && $_POST['perverseness']  == 'on';
  $full_mania   = $ROOM_CONF->full_mania   && $_POST['replace_human'] == 'full_mania';
  $full_cupid   = $ROOM_CONF->full_cupid   && $_POST['replace_human'] == 'full_cupid';
  $chaos        = $ROOM_CONF->chaos        && $_POST['special_role']  == 'chaos';
  $chaosfull    = $ROOM_CONF->chaosfull    && $_POST['special_role']  == 'chaosfull';
  $chaos_hyper  = $ROOM_CONF->chaos_hyper  && $_POST['special_role']  == 'chaos_hyper';
  $quiz         = $ROOM_CONF->quiz         && $_POST['special_role']  == 'quiz';
  $special_role =
    ($ROOM_CONF->duel         && $_POST['special_role']  == 'duel') ||
    ($ROOM_CONF->gray_random  && $_POST['special_role']  == 'gray_random');
  $game_option_list = array();
  $option_role_list = array();
  $check_game_option_list = array('wish_role', 'open_vote', 'open_day', 'not_open_cast');
  $check_option_role_list = array();
  if($quiz){ //クイズ村
    $game_option_list[] = 'quiz';

    //GM ログインパスワードをチェック
    $gm_password = $_POST['gm_password'];
    EscapeStrings($gm_password);
    if($gm_password == ''){
      OutputRoomAction('no_password');
      return false;
    }
    $game_option_list[]    = 'dummy_boy';
    $dummy_boy_handle_name = 'GM';
    $dummy_boy_password    = $gm_password;
  }
  else{
    //身代わり君関連のチェック
    if($ROOM_CONF->dummy_boy && $_POST['dummy_boy'] == 'on'){
      $game_option_list[]       = 'dummy_boy';
      $dummy_boy_handle_name    = '身代わり君';
      $dummy_boy_password       = $SERVER_CONF->system_password;
      $check_option_role_list[] = 'gerd';
    }
    elseif($ROOM_CONF->dummy_boy && $_POST['dummy_boy'] == 'gm_login'){
      //GM ログインパスワードをチェック
      $gm_password = $_POST['gm_password'];
      if($gm_password == ''){
	OutputRoomAction('no_password');
	return false;
      }
      EscapeStrings($gm_password);
      array_push($game_option_list, 'dummy_boy', 'gm_login');
      $dummy_boy_handle_name    = 'GM';
      $dummy_boy_password       = $gm_password;
      $check_option_role_list[] = 'gerd';
    }

    if($chaos || $chaosfull || $chaos_hyper){ //闇鍋モード
      $game_option_list[] = $chaos ? 'chaos' : ($chaosfull ? 'chaosfull' : 'chaos_hyper');
      $check_game_option_list[] = 'secret_sub_role';
      array_push($check_option_role_list, 'topping', 'chaos_open_cast', 'chaos_open_cast_camp',
		 'chaos_open_cast_role');
      if($perverseness){ //天邪鬼村の調整
	$option_role_list[] = 'sub_role_limit';
	$check_option_role_list[] = 'perverseness';
      }
      else{
	$check_option_role_list[] = 'sub_role_limit';
      }
    }
    elseif($special_role){ //特殊配役モード
      $option_role_list[] = $_POST['special_role'];
    }
    else{ //通常村
      array_push($check_option_role_list, 'poison', 'assassin', 'boss_wolf', 'poison_wolf',
		 'possessed_wolf', 'sirius_wolf');
      if(! $full_cupid) $check_option_role_list[] = 'cupid';
      $check_option_role_list[] = 'medium';
      if(! $full_mania) $check_option_role_list[] = 'mania';
      if(! $perverseness) array_push($check_option_role_list, 'decide', 'authority');
    }
    array_push($check_game_option_list, 'deep_sleep', 'mind_open', 'blinder', 'joker', 'festival');
    array_push($check_option_role_list, 'liar', 'gentleman',
	       $perverseness ? 'perverseness' : 'sudden_death', 'critical');
    if(! $special_role) $check_option_role_list[] = 'detective';
  }
  $check_option_role_list[] = 'replace_human';

  //PrintData($_POST, 'Post');
  //PrintData($check_game_option_list, 'CheckGameOption');
  foreach($check_game_option_list as $option){
    if(! $ROOM_CONF->$option) continue;
    if($option == 'not_open_cast'){
      switch($_POST[$option]){
      case 'full':
	$option = 'not_open_cast';
	break;

      case 'auto':
	$option = 'auto_open_cast';
	break;

      default:
	continue 2;
      }
    }
    elseif($_POST[$option] != 'on') continue;
    $game_option_list[] = $option;
  }
  //PrintData($game_option_list);

  //PrintData($check_option_role_list, 'CheckOptionRole');
  foreach($check_option_role_list as $option){
    if(! $ROOM_CONF->$option) continue;

    switch($option){
    case 'replace_human':
      switch($target = $_POST[$option]){
      case 'full_mania':
      case 'full_chiroptera':
      case 'full_cupid':
      case 'replace_human':
	if($ROOM_CONF->$target){
	  $option = $target;
	  break 2;
	}
      }
      continue 2;

    case 'topping':
      $target = $_POST[$option];
      if(array_search($target, $ROOM_CONF->{$option . '_list'}) === false) continue 2;
      $option .= ':' . $target;
      break;

    case 'chaos_open_cast':
      switch($target = $_POST[$option]){
      case 'full':
	break 2;

      case 'camp':
      case 'role':
	$option .= '_' . $target;
	break 2;
      }
      continue 2;

    case 'sub_role_limit':
      switch($target = $_POST[$option]){
      case 'no_sub_role':
      case 'sub_role_limit_easy':
      case 'sub_role_limit_normal':
	if($ROOM_CONF->$target){
	  $option = $target;
	  break 2;
	}
      }
      continue 2;

    default:
      if($_POST[$option] != 'on') continue 2;
    }
    $option_role_list[] = $option;
  }

  if($ROOM_CONF->real_time && $_POST['real_time'] == 'on'){
    $day   = $_POST['real_time_day'];
    $night = $_POST['real_time_night'];

    //制限時間が0から99以内の数字かチェック
    if($day   != '' && ! preg_match('/[^0-9]/', $day)   && $day   > 0 && $day   < 99 &&
       $night != '' && ! preg_match('/[^0-9]/', $night) && $night > 0 && $night < 99){
      $game_option_list[] = 'real_time:' . $day . ':' . $night;
    }
    else{
      OutputRoomAction('time');
      return false;
    }

    if($ROOM_CONF->wait_morning && $_POST['wait_morning'] == 'on'){
      $game_option_list[] = 'wait_morning:';
    }
  }

  //PrintData($game_option_list, 'GameOption');
  //PrintData($option_role_list, 'OptionRole');
  //OutputHTMLFooter(true);

  //テーブルをロック
  if(! LockTable()){
    OutputRoomAction('busy');
    return false;
  }

  //降順にルーム No を取得して最も大きな No を取得
  $room_no = FetchResult('SELECT room_no FROM room ORDER BY room_no DESC') + 1;

  //登録
  $game_option = implode(' ', $game_option_list);
  $option_role = implode(' ', $option_role_list);
  $status = false;

  do{
    if(! $SERVER_CONF->dry_run_mode){
      //村作成
      $time = TZTime();
      $items = 'room_no, room_name, room_comment, establisher_ip, establish_time, ' .
	'game_option, option_role, max_user, status, date, day_night, last_updated';
      $values = "{$room_no}, '{$room_name}', '{$room_comment}', '{$ip_address}', NOW(), " .
	"'{$game_option}', '{$option_role}', {$max_user}, 'waiting', 0, 'beforegame', '{$time}'";
      if(! InsertDatabase('room', $items, $values)) break;

      //身代わり君を入村させる
      if(strpos($game_option, 'dummy_boy') !== false &&
	 FetchResult('SELECT COUNT(uname) FROM user_entry WHERE room_no = ' . $room_no) == 0){
	if(! InsertUser($room_no, 'dummy_boy', $dummy_boy_handle_name, $dummy_boy_password,
			1, in_array('gerd', $option_role_list) ? $USER_ICON->gerd : 0)) break;
      }

      if($SERVER_CONF->secret_room){ //村情報非表示モードの処理
	OutputRoomAction('success', $room_name);
	return true;
      }
    }

    //Twitter 投稿処理
    $twitter = new TwitterConfig();
    $twitter->Send($room_no, $room_name, $room_comment);
    //OutputSiteSummary(); //RSS更新 //テスト中

    OutputRoomAction('success', $room_name);
    $status = true;
  }while(false);
  if(! $status) OutputRoomAction('busy');
  return true;
}

//結果出力 (CreateRoom() 用)
function OutputRoomAction($type, $room_name = ''){
  global $SERVER_CONF;

  switch($type){
  case 'empty':
    OutputActionResultHeader('村作成 [入力エラー]');
    echo 'エラーが発生しました。<br>';
    echo '以下の項目を再度ご確認ください。<br>';
    echo '<ul><li>村の名前が記入されていない。</li>';
    echo '<li>村の説明が記入されていない。</li></ul>';
    break;

  case 'no_password':
    OutputActionResultHeader('村作成 [入力エラー]');
    echo '有効な GM ログインパスワードが設定されていません。<br>';
    break;

  case 'comment':
    OutputActionResultHeader('村作成 [入力エラー]');
    echo 'エラーが発生しました。<br>';
    echo '以下の項目を再度ご確認ください。<br>';
    echo '<ul><li>村の名前・村の説明の文字数が長すぎる</li>';
    echo '<li>村の名前・村の説明に入力禁止文字列が含まれている。</li></ul>';
    break;

  case 'time':
    OutputActionResultHeader('村作成 [入力エラー]');
    echo 'エラーが発生しました。<br>';
    echo '以下の項目を再度ご確認ください。<br>';
    echo '<ul><li>リアルタイム制の昼、夜の時間を記入していない。</li>';
    echo '<li>リアルタイム制の昼、夜の時間を全角で入力している</li>';
    echo '<li>リアルタイム制の昼、夜の時間が0以下、または99以上である</li>';
    echo '<li>リアルタイム制の昼、夜の時間が数字ではない、または異常な文字列</li></ul>';
    break;

  case 'success':
    OutputActionResultHeader('村作成', $SERVER_CONF->site_root);
    echo $room_name . ' 村を作成しました。トップページに飛びます。';
    echo '切り替わらないなら <a href="' . $SERVER_CONF->site_root . '">ここ</a> 。';
    break;

  case 'busy':
    OutputActionResultHeader('村作成 [データベースエラー]');
    echo 'データベースサーバが混雑しています。<br>'."\n";
    echo '時間を置いて再度登録してください。';
    break;

  case 'black_list':
    OutputActionResultHeader('村作成 [制限事項]');
    echo '村立て制限ホストです。';
    break;

  case 'full':
    OutputActionResultHeader('村作成 [制限事項]');
    echo '現在プレイ中の村の数がこのサーバで設定されている最大値を超えています。<br>'."\n";
    echo 'どこかの村で決着がつくのを待ってから再度登録してください。';
    break;

  case 'over_establish':
    OutputActionResultHeader('村作成 [制限事項]');
    echo 'あなたが立てた村が現在稼働中です。<br>'."\n";
    echo '立てた村で決着がつくのを待ってから再度登録してください。';
    break;

  case 'establish_wait':
    OutputActionResultHeader('村作成 [制限事項]');
    echo 'サーバで設定されている村立て時間間隔を経過していません。<br>'."\n";
    echo 'しばらく時間を開けてから再度登録してください。';
    break;

  case 'room_password':
    OutputActionResultHeader('村作成 [制限事項]');
    echo '村作成パスワードが正しくありません。<br>';
    break;
  }
  OutputHTMLFooter(); //フッタ出力
}

//村(room)のwaitingとplayingのリストを出力する
function OutputRoomList(){
  global $DEBUG_MODE, $SERVER_CONF, $ROOM_IMG;

  if($SERVER_CONF->secret_room) return; //シークレットテストモード

  /* RSS機能はテスト中
  if(! $DEBUG_MODE){
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
  $query = "SELECT room_no, room_name, room_comment, game_option, option_role, max_user, status " .
    "FROM room WHERE status <> 'finished' ORDER BY room_no DESC";
  $list = FetchAssoc($query);
  foreach($list as $array){
    extract($array);
    $delete     = $DEBUG_MODE ? $delete_header . $room_no . $delete_footer : '';
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
  OutputRoomOption(array('wish_role', 'real_time', 'wait_morning', 'open_vote', 'open_day'));
  OutputRoomOptionDummyBoy();
  OutputRoomOptionOpenCast();

  $stack = array('poison', 'assassin', 'boss_wolf', 'poison_wolf', 'possessed_wolf',
		 'sirius_wolf', 'cupid', 'medium', 'mania', 'decide', 'authority');
  OutputRoomOption($stack, 'role');

  $stack = array('liar', 'gentleman', 'sudden_death', 'perverseness', 'deep_sleep', 'mind_open',
		 'blinder', 'critical', 'joker', 'detective', 'festival',  'replace_human');
  OutputRoomOption($stack, 'role');

  OutputRoomOption(array('special_role'));
  OutputRoomOptionChaos();

  $password = is_null($SERVER_CONF->room_password) ? '' :
    '村作成パスワード：<input type="password" name="room_password" size="20">　';
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

  $default = 'default_' . $option;
  $checked = property_exists($ROOM_CONF, $default) && $ROOM_CONF->$default ? ' checked' : '';
  if($label != '') $label .= '_';
  $label .= $option;

  $sentence = $GAME_OPT_MESS->$option;
  if(property_exists($CAST_CONF, $option) && is_int($limit = $CAST_CONF->$option)){
    $sentence .= ' ('  . $limit . '人～)';
  }

  $caption = property_exists($GAME_OPT_CAPT, $option) ? $GAME_OPT_CAPT->$option : '';
  switch($option){
  case 'room_name':
  case 'room_comment':
  case 'gm_password':
    return GenerateTextForm($option);

  case 'max_user':
  case 'replace_human':
  case 'special_role':
  case 'topping':
    return GenerateSelector($option);

  case 'real_time':
    $caption .= <<<EOF
　昼：
<input type="text" name="real_time_day" value="{$TIME_CONF->default_day}" size="2" maxlength="2">分 夜：
<input type="text" name="real_time_night" value="{$TIME_CONF->default_night}" size="2" maxlength="2">分
EOF;
    break;
  }

  return <<<EOF
<tr>
<td><label for="{$label}">{$sentence}：</label></td>
<td class="explain">
<input id="{$label}" type="checkbox" name="{$option}" value="on"{$checked}>
({$caption})
</td>
</tr>

EOF;
}

//村作成フォーム生成 (テキスト型)
function GenerateTextForm($option){
  global $ROOM_CONF, $GAME_OPT_MESS, $GAME_OPT_CAPT;

  $footer = '';
  switch($option){
  case 'room_name':
    $footer = ' 村';
    break;

  case 'gm_password':
    $footer = '<span class="explain">' . $GAME_OPT_CAPT->gm_password . '</span>';
    break;
  }
  return <<<EOF
<tr>
<td><label>{$GAME_OPT_MESS->$option}：</label></td>
<td><input type="text" name="{$option}" size="{$ROOM_CONF->{$option . '_input'}}">{$footer}</td>
</tr>

EOF;
}

//村作成フォーム生成 (セレクタ型)
function GenerateSelector($option){
  global $ROOM_CONF, $GAME_OPT_MESS, $GAME_OPT_CAPT;

  switch($option){
  case 'max_user':
    $label = '最大人数';
    $str = '';
    foreach($ROOM_CONF->{$option . '_list'} as $number){
      $str .= '<option value="' . $number . '"' .
	($number == $ROOM_CONF->default_max_user ? ' selected' : '') . '>' .
	$number . '</option>'."\n";
    }
    break;

  case 'replace_human':
  case 'special_role':
    $label = 'モード名';
    $str = '<option value="" selected>なし</option>';
    foreach($ROOM_CONF->{$option . '_list'} as $role){
      if($ROOM_CONF->$role){
	$str .= '<option value="' . $role . '">' . $GAME_OPT_MESS->$role . '</option>'."\n";
      }
    }
    break;

  case 'topping':
    $label = 'タイプ名';
    $str = '<option value="" selected>なし</option>';
    foreach($ROOM_CONF->{$option . '_list'} as $mode){
      $role = 'topping_' . $mode;
      if($GAME_OPT_MESS->$role){
	$str .= '<option value="' . $mode . '">' . $GAME_OPT_MESS->$role . '</option>'."\n";
      }
    }
    break;
  }

  return <<<EOF
<tr>
<td><label>{$GAME_OPT_MESS->$option}：</label></td>
<td>
<select name="{$option}">
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
    $checked_dummy_boy = ' checked';
  elseif($ROOM_CONF->default_gm_login)
    $checked_gm_login = ' checked';
  else
    $checked_nothing = ' checked';

  echo <<<EOF
<tr><td colspan="2"><hr></td></tr>
<tr>
<td><label>{$GAME_OPT_MESS->dummy_boy}：</label></td>
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
    $checked_close = ' checked';
    break;

  case 'auto':
    if($ROOM_CONF->auto_open_cast){
      $checked_auto = ' checked';
      break;
    }

  default:
    $checked_open = ' checked';
    break;
  }

  echo <<<EOF
<tr><td colspan="2"><hr></td></tr>
<tr>
<td><label>{$GAME_OPT_MESS->not_open_cast}：</label></td>
<td class="explain">
<input type="radio" name="not_open_cast" value=""{$checked_open}>
{$GAME_OPT_CAPT->no_close_cast}<br>

<input type="radio" name="not_open_cast" value="full"{$checked_close}>
{$GAME_OPT_CAPT->not_open_cast}<br>

EOF;

  if($ROOM_CONF->auto_open_cast){
    echo <<<EOF
<input type="radio" name="not_open_cast" value="auto"{$checked_auto}>
{$GAME_OPT_CAPT->auto_open_cast}
</td>

EOF;
  }
}

//村作成フォーム出力 (闇鍋モード用)
function OutputRoomOptionChaos(){
  global $ROOM_CONF, $GAME_OPT_MESS, $GAME_OPT_CAPT;

  if(! $ROOM_CONF->chaos) return NULL;

  OutputRoomOption(array('topping'));
  if($ROOM_CONF->chaos_open_cast){
    $checked_chaos_open_cast_full = '';
    $checked_chaos_open_cast_camp = '';
    $checked_chaos_open_cast_role = '';
    $checked_chaos_open_cast_none = '';
    switch($ROOM_CONF->default_chaos_open_cast){
    case 'full':
      $checked_chaos_open_cast_full = ' checked';
      break;

    case 'camp':
      $checked_chaos_open_cast_camp = ' checked';
      break;

    case 'role':
      $checked_chaos_open_cast_role = ' checked';
      break;

    default:
      $checked_chaos_open_cast_none = ' checked';
      break;
    }

    echo <<<EOF
<tr>
<td><label>{$GAME_OPT_MESS->chaos_open_cast}：</label></td>
<td class="explain">
<input type="radio" name="chaos_open_cast" value=""{$checked_chaos_open_cast_none}>
{$GAME_OPT_CAPT->chaos_not_open_cast}<br>

<input type="radio" name="chaos_open_cast" value="camp"{$checked_chaos_open_cast_camp}>
{$GAME_OPT_CAPT->chaos_open_cast_camp}<br>

<input type="radio" name="chaos_open_cast" value="role"{$checked_chaos_open_cast_role}>
{$GAME_OPT_CAPT->chaos_open_cast_role}<br>

<input type="radio" name="chaos_open_cast" value="full"{$checked_chaos_open_cast_full}>
{$GAME_OPT_CAPT->chaos_open_cast_full}
</td>
</tr>

EOF;
  }

  if($ROOM_CONF->sub_role_limit){
    $checked_no_sub_role           = '';
    $checked_sub_role_limit_easy   = '';
    $checked_sub_role_limit_normal = '';
    $checked_sub_role_limit_none   = '';
    switch($ROOM_CONF->default_sub_role_limit){
    case 'no':
      $checked_no_sub_role = ' checked';
      break;

    case 'easy':
      $checked_sub_role_limit_easy = ' checked';
      break;

    case 'normal':
      $checked_sub_role_limit_normal = ' checked';
      break;

    default:
      $checked_sub_role_limit_none = ' checked';
      break;
    }

    echo <<<EOF
<tr>
<td><label>{$GAME_OPT_MESS->sub_role_limit}：</label></td>
<td class="explain">
<input type="radio" name="sub_role_limit" value="no_sub_role"{$checked_no_sub_role}>
{$GAME_OPT_CAPT->no_sub_role}<br>

<input type="radio" name="sub_role_limit" value="sub_role_limit_easy"{$checked_sub_role_limit_easy}>
{$GAME_OPT_CAPT->sub_role_limit_easy}<br>

<input type="radio" name="sub_role_limit" value="sub_role_limit_normal"{$checked_sub_role_limit_normal}>
{$GAME_OPT_CAPT->sub_role_limit_normal}<br>

<input type="radio" name="sub_role_limit" value=""{$checked_sub_role_limit_none}>
{$GAME_OPT_CAPT->sub_role_limit_none}<br>
</td>
</tr>

EOF;
  }
  OutputRoomOption(array('secret_sub_role'), '', false);
}
