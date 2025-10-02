<?php
require_once(dirname(__FILE__) . '/include/game_functions.php');

//セッション開始
session_start();
$session_id = session_id();

//引数を取得
$room_no       = (int)$_GET['room_no'];
$log_mode      = $_GET['log_mode'];
$get_date      = (int)$_GET['date'];
$get_day_night = $_GET['day_night'];

$dbHandle = ConnectDatabase(); //DB 接続
$uname = CheckSession($session_id); //セッション ID をチェック

//日付とシーンを取得
$sql = mysql_query("SELECT date, day_night, room_name, room_comment, game_option, status
			FROM room WHERE room_no = $room_no");
$array   = mysql_fetch_assoc($sql);
$date         = $array['date'];
$day_night    = $array['day_night'];
$room_name    = $array['room_name'];
$room_comment = $array['room_comment'];
$game_option  = $array['game_option'];
$status       = $array['status'];

//自分のハンドルネーム、役割、生存を取得
$sql = mysql_query("SELECT user_no, handle_name, sex, role, live FROM user_entry
			WHERE room_no = $room_no AND uname = '$uname' AND user_no > 0");
$array  = mysql_fetch_assoc($sql);
$user_no     = $array['user_no'];
$handle_name = $array['handle_name'];
$sex         = $array['sex'];
$role        = $array['role'];
$live        = $array['live'];

if($live != 'dead' && $day_night != 'aftergame'){ //死者かゲーム終了後だけ
  OutputActionResult('ユーザ認証エラー',
		     'ログ閲覧許可エラー<br>' .
		     '<a href="index.php" target="_top">トップページ</a>' .
		     'からログインしなおしてください');
}

$live = 'dead';
$date = $get_date;
$day_night = $get_day_night;

OutputGamePageHeader(); //HTMLヘッダ
echo '<table><tr><td width="1000" align="right">ログ閲覧 ' . $date . ' 日目 (' .
  ($day_night == 'day' ? '昼' : '夜') . ')</td></tr></table>'."\n";
//OutputPlayerList();    //プレイヤーリスト
OutputTalkLog();       //会話ログ
OutputAbilityAction(); //能力発揮
OutputDeadMan();       //死亡者
if($day_night == 'night') OutputVoteList(); //投票結果
OutputHTMLFooter();
DisconnectDatabase($dbHandle); //DB 接続解除
?>
