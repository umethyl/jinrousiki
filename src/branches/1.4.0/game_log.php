<?php
require_once('include/init.php');
$INIT_CONF->LoadFile('user_class', 'talk_class');
$INIT_CONF->LoadClass('SESSION', 'ROLES');

//-- データ収集 --//
$INIT_CONF->LoadRequest('RequestGameLog'); //引数を取得
$DB_CONF->Connect(); //DB 接続
$SESSION->Certify(); //セッション認証

$ROOM = new Room($RQ_ARGS); //村情報を取得
$ROOM->log_mode = true;
$ROOM->single_log_mode = true;

$USERS = new UserDataSet($RQ_ARGS); //ユーザ情報を取得
$SELF = $USERS->BySession(); //自分の情報をロード

if(! ($SELF->IsDead() || $ROOM->IsAfterGame())){ //死者かゲーム終了後だけ
  OutputActionResult('ログ閲覧認証エラー',
		     'ログ閲覧認証エラー：<a href="./" target="_top">トップページ</a>' .
		     'からログインしなおしてください');
}
if($ROOM->date < $RQ_ARGS->date ||
   ($ROOM->date == $RQ_ARGS->date && ($ROOM->IsDay() || $ROOM->day_night == $RQ_ARGS->day_night))){
  OutputActionResult('入力データエラー', '入力データエラー：無効な日時です');
}

$ROOM->date      = $RQ_ARGS->date;
$ROOM->day_night = $RQ_ARGS->day_night;
$USERS->SetEvent(true);

//-- ログ出力 --//
OutputGamePageHeader(); //HTMLヘッダ

echo '<table><tr><td width="1000" align="right">ログ閲覧 ' . $ROOM->date . ' 日目 (' .
  ($ROOM->IsBeforeGame() ? '開始前' : ($ROOM->IsDay() ? '昼' : '夜')) . ')</td></tr></table>'."\n";

OutputTalkLog();       //会話ログ
OutputAbilityAction(); //能力発揮
OutputLastWords();     //遺言
OutputDeadMan();       //死亡者
if($ROOM->IsNight()) OutputVoteList(); //投票結果
OutputHTMLFooter(); //HTMLフッタ
