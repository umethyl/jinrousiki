<?php
require_once('include/init.php');
$INIT_CONF->LoadFile('room_class', 'user_class', 'icon_functions');
$INIT_CONF->LoadClass('SESSION', 'ROOM_CONF', 'GAME_CONF', 'MESSAGE');
$INIT_CONF->LoadRequest('RequestUserManager'); //引数を取得
$DB_CONF->Connect(); //DB 接続
$RQ_ARGS->entry ? EntryUser() : OutputEntryUserPage();
$DB_CONF->Disconnect(); //DB 接続解除

//-- 関数 --//
//ユーザを登録する
function EntryUser(){
  global $DEBUG_MODE, $GAME_CONF, $MESSAGE, $RQ_ARGS, $SESSION;

  extract($RQ_ARGS->ToArray()); //引数を取得

  //記入漏れチェック
  $title = '村人登録 [入力エラー]';
  $sentence = 'が空です (空白と改行コードは自動で削除されます)';
  if($uname == '')       OutputActionResult($title, 'ユーザ名'     . $sentence);
  if($handle_name == '') OutputActionResult($title, '村人の名前'   . $sentence);
  if($password == '')    OutputActionResult($title, 'パスワード'   . $sentence);
  if($profile == '')     OutputActionResult($title, 'プロフィール' . $sentence);
  if(empty($sex))        OutputActionResult($title, '性別が入力されていません');
  if(empty($icon_no))    OutputActionResult($title, 'アイコン番号が入力されていません');

  //文字数制限チェック
  if(strlen($uname) > $GAME_CONF->entry_uname_limit){
    OutputActionResult($title, 'ユーザ名は' . $GAME_CONF->entry_uname_limit . '文字まで');
  }
  if(strlen($handle_name) > $GAME_CONF->entry_uname_limit){
    OutputActionResult($title, '村人の名前は' . $GAME_CONF->entry_uname_limit . '文字まで');
  }
  if(strlen($profile) > $GAME_CONF->entry_profile_limit){
    OutputActionResult($title, 'プロフィールは' . $GAME_CONF->entry_profile_limit . '文字まで');
  }

  //例外チェック
  if($uname == 'dummy_boy' || $uname == 'system'){
    OutputActionResult($title, 'ユーザ名「' . $uname . '」は使用できません');
  }
  if($handle_name == '身代わり君' || $handle_name == 'システム'){
    OutputActionResult($title, '村人名「' . $handle_name . '」は使用できません');
  }
  if($sex != 'male' && $sex != 'female') OutputActionResult($title, '無効な性別です');

  $query = 'SELECT COUNT(icon_no) FROM user_icon WHERE icon_no = ' . $icon_no;
  if($icon_no < 1 || FetchResult($query) < 1) OutputActionResult($title, '無効なアイコン番号です');

  //テーブルをロック
  if(! LockTable()){
    OutputActionResult('村人登録 [サーバエラー]',
		       'サーバが混雑しています。<br>'."\n".'再度登録してください');
  }

  //項目被りチェック
  //比較演算子は大文字・小文字を区別しないのでクエリで直に判定する
  $query = "SELECT COUNT(uname) FROM user_entry WHERE room_no = {$room_no} AND ";

  //キックされた人と同じユーザ名
  if(FetchResult($query . "uname = '{$uname}' AND live = 'kick'") > 0){
    OutputActionResult('村人登録 [キックされたユーザ]',
		       'キックされた人と同じユーザ名は使用できません。 (村人名は可)<br>'."\n" .
		       '別の名前にしてください。', '', true);
  }

  //ユーザ名、村人名
  $query .= "live = 'live' AND ";
  if(FetchResult($query . "(uname = '{$uname}' OR handle_name = '{$handle_name}')") > 0){
    OutputActionResult('村人登録 [重複登録エラー]',
		       'ユーザ名、または村人名が既に登録してあります。<br>'."\n" .
		       '別の名前にしてください。', '', true);
  }
  //OutputActionResult('トリップテスト', $uname . '<br>' . $handle_name);

  //IPアドレスチェック
  $ip_address = $_SERVER['REMOTE_ADDR']; //ユーザのIPアドレスを取得
  if(! $DEBUG_MODE){
    if(CheckBlackList()){
      OutputActionResult('村人登録 [入村制限]', '入村制限ホストです。', '', true);
    }
    elseif($GAME_CONF->entry_one_ip_address &&
	   FetchResult("{$query} ip_address = '{$ip_address}'") > 0){
      OutputActionResult('村人登録 [多重登録エラー]', '多重登録はできません。', '', true);
    }
  }

  //DBから最大人数を取得
  $ROOM = RoomDataSet::LoadEntryUser($room_no);

  $request = new RequestBase();
  $request->room_no = $room_no;
  $request->entry_user = true;
  $USERS = new UserDataSet($request);
  //PrintData($USERS); //テスト用

  $ROOM = RoomDataSet::LoadEntryUser($room_no); //DBから最大人数を取得
  $user_no = count($USERS->names) + 1; //KICK された住人も含めた新しい番号を振る
  $user_count = $USERS->GetUserCount(); //現在の KICK されていない住人の数を取得

  //定員オーバーしているとき
  if($user_count >= $ROOM->max_user){
    OutputActionResult('村人登録 [入村不可]', '村が満員です。', '', true);
  }
  if(! $ROOM->IsBeforeGame() || $ROOM->status != 'waiting'){
    OutputActionResult('村人登録 [入村不可]', 'すでにゲームが開始されています', '', true);
  }

  //クッキーの削除
  $ROOM->system_time = TZTime(); //現在時刻を取得
  $cookie_time = $ROOM->system_time - 3600;
  setcookie('day_night',  '', $cookie_time);
  setcookie('vote_times', '', $cookie_time);
  setcookie('objection',  '', $cookie_time);

  //DB にユーザデータを登録
  if(InsertUser($room_no, $uname, $handle_name, $password, $user_no, $icon_no, $profile,
		$sex, $role, $SESSION->Get(true))){
    $ROOM->Talk($handle_name . ' ' . $MESSAGE->entry_user); //入村メッセージ
    $url = 'game_frame.php?room_no=' . $room_no;
    $user_count++;
    OutputActionResult('村人登録',
		       $user_count . ' 番目の村人登録完了、村の寄り合いページに飛びます。<br>'."\n" .
		       '切り替わらないなら <a href="' . $url. '">ここ</a> 。',
		       $url, true);
  }
  else{
    OutputActionResult('村人登録 [データベースサーバエラー]',
		       'データベースサーバが混雑しています。<br>'."\n" .
		       '時間を置いて再度登録してください。', '', true);
  }
  UnlockTable(); //ロック解除
}

//ユーザ登録画面表示
function OutputEntryUserPage(){
  global $SERVER_CONF, $GAME_CONF, $ICON_CONF, $ROLE_DATA, $RQ_ARGS;

  extract($RQ_ARGS->ToArray()); //引数を取得
  $ROOM = RoomDataSet::LoadEntryUserPage($room_no);
  $sentence = $room_no . ' 番地の村は';
  if(is_null($ROOM->id))  OutputActionResult('村人登録 [村番号エラー]', $sentence . '存在しません');
  if($ROOM->IsFinished()) OutputActionResult('村人登録 [入村不可]',  $sentence . '終了しました');
  if($ROOM->status != 'waiting'){
    OutputActionResult('村人登録 [入村不可]', $sentence . 'すでにゲームが開始されています。');
  }
  $ROOM->ParseOption(true);
  $trip = '(トリップ使用' . ($GAME_CONF->trip ? '可能' : '不可') . ')';

  OutputHTMLHeader($SERVER_CONF->title .'[村人登録]', 'entry_user');
  $male_checked   = '';
  $female_checked = '';
  switch($RQ_ARGS->sex){
  case 'male':
    $male_checked   = ' checked=""';
    break;

  case 'female':
    $female_checked = ' checked=""';
    break;
  }
  echo <<<EOF
<script type="text/javascript" src="javascript/submit_icon_search.js"></script>
</head>
<body>
<a href="./">←戻る</a><br>
<form method="POST" action="user_manager.php?room_no={$ROOM->id}">
<div align="center">
<table class="main">
<tr><td><img src="img/entry_user/title.gif" alt="申請書"></td></tr>
<tr><td class="title">{$ROOM->name} 村<img src="img/entry_user/top.gif" alt="への住民登録を申請します"></td></tr>
<tr><td class="number">～{$ROOM->comment}～ [{$ROOM->id} 番地]</td></tr>
<tr><td>
<table class="input">
<tr>
<td class="img"><img src="img/entry_user/uname.gif" alt="ユーザ名"></td>
<td><input type="text" name="uname" size="30" maxlength="30" value="{$RQ_ARGS->uname}"></td>
<td class="explain">普段は表示されず、他のユーザ名がわかるのは<br>死亡したときとゲーム終了後のみです{$trip}</td>
</tr>
<tr>
<td class="img"><img src="img/entry_user/handle_name.gif" alt="村人の名前"></td>
<td><input type="text" name="handle_name" size="30" maxlength="30" value="{$RQ_ARGS->handle_name}"></td>
<td class="explain">村で表示される名前です</td>
</tr>
<tr>
<td class="img"><img src="img/entry_user/password.gif" alt="パスワード"></td>
<td><input type="password" name="password" size="30" maxlength="30"></td>
<td class="explain">セッションが切れた場合のログイン時に使います<br> (暗号化されていないので要注意)</td>
</tr>
<tr>
<td class="img"><img src="img/entry_user/sex.gif" alt="性別"></td>
<td class="img">
<label for="male"><img src="img/entry_user/sex_male.gif" alt="男性"><input type="radio" id="male" name="sex" value="male"{$male_checked}></label>
<label for="female"><img src="img/entry_user/sex_female.gif" alt="女性"><input type="radio" id="female" name="sex" value="female"{$female_checked}></label>
</td>
<td class="explain">特に意味は無いかも……</td>
</tr>
<tr>
<td class="img"><img src="img/entry_user/profile.gif" alt="プロフィール"></td>
<td colspan="2">
<textarea name="profile" cols="30" rows="2">{$RQ_ARGS->profile}</textarea>
<input type="hidden" name="role" value="none">
</td>
</tr>

EOF;

  if($ROOM->IsOption('wish_role')){
    echo <<<EOF
<tr>
<td class="role"><img src="img/entry_user/role.gif" alt="役割希望"></td>
<td colspan="2">

EOF;

    $wish_role_list = array('none');
    if($ROOM->IsChaosWish()){
      array_push($wish_role_list, 'human', 'mage', 'necromancer', 'medium', 'priest', 'guard',
		 'common', 'poison', 'poison_cat', 'pharmacist', 'assassin', 'mind_scanner',
		 'jealousy', 'brownie', 'doll', 'escaper', 'wolf', 'mad', 'fox', 'child_fox',
		 'cupid', 'angel', 'quiz', 'vampire', 'chiroptera', 'fairy', 'ogre', 'yaksa',
		 'mania', 'unknown_mania');
    }
    elseif($ROOM->IsOption('gray_random')){
      array_push($wish_role_list, 'human', 'wolf', 'mad', 'fox');
    }
    else{
      array_push($wish_role_list,  'human', 'wolf');
      if($ROOM->IsQuiz()){
	array_push($wish_role_list, 'mad', 'common', 'fox');
      }
      else{
	array_push($wish_role_list, 'mage', 'necromancer', 'mad', 'guard', 'common');
	if($ROOM->IsOption('detective')) $wish_role_list[] = 'detective_common';
	$wish_role_list[] = 'fox';
      }
      if($ROOM->IsOption('poison')) $wish_role_list[] = 'poison';
      if($ROOM->IsOption('assassin')) $wish_role_list[] = 'assassin';
      if($ROOM->IsOption('boss_wolf')) $wish_role_list[] = 'boss_wolf';
      if($ROOM->IsOption('poison_wolf')){
	array_push($wish_role_list, 'poison_wolf', 'pharmacist');
      }
      if($ROOM->IsOption('possessed_wolf')) $wish_role_list[] = 'possessed_wolf';
      if($ROOM->IsOption('sirius_wolf')) $wish_role_list[] = 'sirius_wolf';
      if($ROOM->IsOption('cupid')) $wish_role_list[] = 'cupid';
      if($ROOM->IsOption('medium')) array_push($wish_role_list, 'medium', 'mind_cupid');
      if($ROOM->IsOptionGroup('mania') && ! in_array('mania', $wish_role_list)){
	$wish_role_list[] = 'mania';
      }
    }

    echo "<table>\n<tr>";
    $count = 0;
    foreach($wish_role_list as $role){
      if($count > 0 && $count % 4 == 0) echo "</tr>\n<tr>"; //4個ごとに改行
      $count++;
      $alt = '←' . ($role == 'none' ? '無し' : $ROLE_DATA->main_role_list[$role]);
      $checked = $RQ_ARGS->role == $role ? ' checked' : '';
      echo <<<EOF
<td><label for="{$role}"><input type="radio" id="{$role}" name="role" value="{$role}"{$checked}><img src="img/entry_user/role_{$role}.gif" alt="{$alt}"></label></td>
EOF;
    }
    echo "</tr>\n</table>\n</td>";
  }
  else{
    echo '<input type="hidden" name="role" value="none">';
  }

  echo <<<EOF
</tr>
<tr>
<td class="submit" colspan="3">
<span class="explain">
ユーザ名、村人の名前、パスワードの前後の空白および改行コードは自動で削除されます
</span>
<input type="submit" id="entry" name="entry" value="村人登録申請"></td>
</tr>
</table>
</td></tr>

<tr><td>
<fieldset><legend><img src="img/entry_user/icon.gif" alt="アイコン"></legend>
<table class="icon">
<tr><td colspan="5">
<input id="fix_number" type="radio" name="icon_no"><label for="fix_number">手入力</label>
<input type="text" name="icon_no" size="10px">(半角英数で入力してください)
</td></tr>
<tr><td colspan="5">

EOF;
  OutputIconList('user_manager');
  echo <<<EOF
</tr></table>
</fieldset>
</td></tr>

</table></div></form>
</body></html>

EOF;
}
