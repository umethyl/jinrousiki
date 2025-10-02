<?php
require_once(dirname(__FILE__) . '/include/functions.php');

if(! $dbHandle = ConnectDatabase(true, false)) return false; //DB 接続

MaintenanceRoom();
EncodePostData();

if($_POST['command'] == 'CREATE_ROOM'){
  //リファラチェック
  if(strncmp(@$_SERVER['HTTP_REFERER'], $site_root, strlen($site_root)) != 0)
    OutputActionResult('村作成 [入力エラー]', '無効なアクセスです。');
  //村作成パスワードチェック
  elseif($ROOM_CONF->room_password != '' && $_POST['room_password'] != $ROOM_CONF->room_password)
    OutputActionResult('村作成 [制限事項]', '村作成パスワードが正しくありません。');
  //指定された人数の配役があるかチェック
  elseif (!in_array($_POST['max_user'], $ROOM_CONF->max_user_list))
     OutputActionResult('村作成 [入力エラー]', '無効な最大人数です。');
  else
    CreateRoom($_POST['room_name'], $_POST['room_comment'], $_POST['max_user']);
}
else{
  OutputRoomList();
}

DisconnectDatabase($dbHandle); //DB 接続解除

//-- 関数 --//
//村のメンテナンス処理
function MaintenanceRoom(){
  global $ROOM_CONF;

  //一定時間更新の無い村は廃村にする
  $list  = mysql_query("SELECT room_no, last_updated FROM room WHERE status <> 'finished'");
  $query = "UPDATE room SET status = 'finished', day_night = 'aftergame' WHERE room_no = ";
  MaintenanceRoomAction($list, $query, $ROOM_CONF->die_room);

  //終了した部屋のセッションIDのデータをクリアする
  $list = mysql_query("SELECT room.room_no, last_updated from room, user_entry
			WHERE room.room_no = user_entry.room_no
			AND !(user_entry.session_id is NULL) GROUP BY room_no");
  $query = "UPDATE user_entry SET session_id = NULL WHERE room_no = ";
  MaintenanceRoomAction($list, $query, $ROOM_CONF->clear_session_id);
}

//村のメンテナンス処理 (実体)
function MaintenanceRoomAction($list, $query, $base_time){
  $count = mysql_num_rows($list);
  $time  = TZTime();

  for($i=0; $i < $count; $i++){
    $array = mysql_fetch_assoc($list);
    $room_no      = $array['room_no'];
    $last_updated = $array['last_updated'];
    $diff_time    = $time - $last_updated;
    if($diff_time > $base_time) mysql_query($query . $room_no);
  }
}

//村(room)の作成
function CreateRoom($room_name, $room_comment, $max_user){
  global $MESSAGE, $system_password;

  //入力データのエラーチェック
  if($room_name == '' || $room_comment == '' || ! ctype_digit($max_user)){
    OutputRoomAction('empty');
    return false;
  }

  if($_POST['game_option_real_time'] == 'real_time'){
    $day   = $_POST['game_option_real_time_day'];
    $night = $_POST['game_option_real_time_night'];

    //制限時間が0から99以内の数字かチェック
    if($day   != '' && ! preg_match('/[^0-9]/', $day)   && $day   > 0 && $day   < 99 &&
       $night != '' && ! preg_match('/[^0-9]/', $night) && $night > 0 && $night < 99){
      $real_time_set_str = 'real_time:' . $day . ':' . $night;
    }
    else{
      OutputRoomAction('time');
      return false;
    }
  }

  $option_role = $_POST['option_role_decide'] . ' ' . $_POST['option_role_authority'] .
    ' ' . $_POST['option_role_poison'] . ' ' . $_POST['option_role_cupid'];

  $game_option = $_POST['game_option_wish_role'] . ' ' . $_POST['game_option_dummy_boy'] .
    ' ' . $_POST['game_option_open_vote'] . ' ' . $_POST['game_option_not_open_cast'] .
    ' ' . $real_time_set_str;

  if(! mysql_query('LOCK TABLES room WRITE, user_entry WRITE, vote WRITE, talk WRITE')){
    OutputRoomAction('busy');
    return false;
  }

  $result = mysql_query('SELECT room_no FROM room ORDER BY room_no DESC'); //降順にルームNoを取得
  $room_no_array = mysql_fetch_assoc($result); //一行目(最も大きなNo)を取得
  $room_no = $room_no_array['room_no'] + 1;

  //エスケープ処理
  EscapeStrings($room_name);
  EscapeStrings($room_comment);

  //登録
  $time = TZTime();
  $entry = mysql_query("INSERT INTO room(room_no, room_name, room_comment, game_option,
			option_role, max_user, status, date, day_night, last_updated)
			VALUES($room_no, '$room_name', '$room_comment', '$game_option',
			'$option_role', $max_user, 'waiting', 0, 'beforegame', '$time')");

  //身代わり君を入村させる
  if(strpos($game_option, 'dummy_boy') !== false){
    mysql_query("INSERT INTO user_entry(room_no, user_no, uname, handle_name, icon_no,
		   profile, sex, password, live, last_words, ip_address)
		   VALUES($room_no, 1, 'dummy_boy', '身代わり君', 0, '{$MESSAGE->dummy_boy_comment}',
		   'male', '$system_password', 'live', '{$MESSAGE->dummy_boy_last_words}', '')");
  }

  if($entry && mysql_query('COMMIT')){ //一応コミット
    OutputRoomAction('success', $room_name);
  }
  else{
    OutputRoomAction('busy');
  }
  mysql_query('UNLOCK TABLES');
}

//結果出力 (CreateRoom() 用)
function OutputRoomAction($type, $room_name = ''){
  switch($type){
    case 'empty':
      OutputActionResultHeader('村作成 [入力エラー]');
      echo 'エラーが発生しました。<br>';
      echo '以下の項目を再度ご確認ください。<br>';
      echo '<ul><li>村の名前が記入されていない。</li>';
      echo '<li>村の説明が記入されていない。</li>';
      echo '<li>最大人数が数字ではない、または異常な文字列。</li></ul>';
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
      OutputActionResultHeader('村作成', 'index.php');
      echo "$room_name 村を作成しました。トップページに飛びます。";
      echo '切り替わらないなら <a href="index.php">ここ</a> 。';
      break;

    case 'busy':
      OutputActionResultHeader('村作成 [データベースエラー]');
      echo 'データベースサーバが混雑しています。<br>'."\n";
      echo '時間を置いて再度登録してください。';
      break;
  }
  OutputHTMLFooter(); //フッタ出力
}

//村(room)のwaitingとplayingのリストを出力する
function OutputRoomList(){
  global $DEBUG_MODE, $ROOM_IMG;

  //ルームNo、ルーム名、コメント、最大人数、状態を取得
  $sql = mysql_query("SELECT room_no, room_name, room_comment, game_option, option_role, max_user,
			status FROM room WHERE status <> 'finished' ORDER BY room_no DESC ");
  if($sql == NULL) return false;

  while($array = mysql_fetch_assoc($sql)){
    $room_no      = $array['room_no'];
    $room_name    = $array['room_name'];
    $room_comment = $array['room_comment'];
    $game_option  = $array['game_option'];
    $option_role  = $array['option_role'];
    $max_user     = $array['max_user'];
    $status       = $array['status'];

    switch($status){
      case 'waiting':
	$status_img = $ROOM_IMG->waiting;
	break;

      case 'playing':
	$status_img = $ROOM_IMG->playing;
	break;
    }

    $option_img_str = ''; //ゲームオプションの画像
    if(strpos($game_option, 'wish_role') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->wish_role, '役割希望制');
    if(strpos($game_option, 'real_time') !== false){
      //実時間の制限時間を取得
      $real_time_str = strstr($game_option, 'real_time');
      sscanf($real_time_str, "real_time:%d:%d", $day, $night);
      AddImgTag($option_img_str, $ROOM_IMG->real_time,
		"リアルタイム制　昼： $day 分　夜： $night 分");
    }
    if(strpos($game_option, 'dummy_boy') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->dummy_boy, '初日の夜は身代わり君');
    if(strpos($game_option, 'open_vote') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->open_vote, '投票した票数を公表する');
    if(strpos($game_option, 'not_open_cast') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->not_open_cast, '霊界で配役を公開しない');
    if(strpos($option_role, 'decide') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->decide, '16人以上で決定者登場');
    if(strpos($option_role, 'authority') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->authority, '16人以上で権力者登場');
    if(strpos($option_role, 'poison') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->poison, '20人以上で埋毒者登場');
    if(strpos($option_role, 'cupid') !== false)
      AddImgTag($option_img_str, $ROOM_IMG->cupid, 'キューピッド登場');

    $max_user_img = $ROOM_IMG -> max_user_list[$max_user]; //最大人数

    echo <<<EOF
<a href="login.php?room_no=$room_no">
<img src="$status_img"><span>[{$room_no}番地]</span>{$room_name}村<br>
<div>~{$room_comment}~ {$option_img_str}<img src="$max_user_img"></div>
</a><br>

EOF;

    if($DEBUG_MODE){
      echo '<a href="admin/room_delete.php?room_no=' . $room_no . '">' . $room_no .
	' 番地を削除 (緊急用)</a><br>'."\n";
    }
  }
}

//オプション画像タグ追加 (OutputRoomList() 用)
function AddImgTag(&$tag, $src, $title){
  $tag .= "<img class=\"option\" src=\"$src\" title=\"$title\" alt=\"$title\">";
}
?>
