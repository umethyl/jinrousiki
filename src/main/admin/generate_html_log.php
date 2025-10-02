<?php
define('JINRO_ROOT', '..');
require_once(JINRO_ROOT . '/include/init.php');

$disable = true; //使用時には false に変更する
//$disable = false;
if($disable){
  OutputActionResult('認証エラー', 'このスクリプトは使用できない設定になっています。');
}

$INIT_CONF->LoadFile('oldlog_functions');
$INIT_CONF->LoadClass('ROOM_CONF', 'CAST_CONF', 'ROOM_IMG', 'GAME_OPT_MESS');
$INIT_CONF->LoadRequest('RequestOldLog'); //引数を取得
$DB_CONF->ChangeName($RQ_ARGS->db_no); //DB 名をセット
$DB_CONF->Connect(); //DB 接続

$RQ_ARGS->generate_index = true;
$RQ_ARGS->index_no = 1; //インデックスページの開始番号 (現在は 1 で固定)
$RQ_ARGS->min_room_no = 1; //インデックス化する村の開始番号 (現在は 1 で固定)
$RQ_ARGS->max_room_no = 200; //インデックス化する村の終了番号
$RQ_ARGS->prefix = ''; //各ページの先頭につける文字列 (テスト / 上書き回避用)
$RQ_ARGS->add_role = true;

$db_delete_mode = false; //部屋削除のみ
if($db_delete_mode){
  OutputHTMLHeader('DB削除モード');
  for($i = $RQ_ARGS->min_room_no; $i <= $RQ_ARGS->max_room_no; $i++){
    DeleteRoom($i);
    echo "{$i} 番地を削除しました<br>";
  }
  OptimizeTable();
  OutputHTMLFooter(true);
}

GenerateLogIndex(); //インデックスページ生成

$INIT_CONF->LoadFile('game_play_functions', 'user_class', 'talk_class');
$INIT_CONF->LoadClass('ROLES', 'ICON_CONF', 'VICT_MESS');

$room_delete = false; //DB削除設定
for($i = $RQ_ARGS->min_room_no; $i <= $RQ_ARGS->max_room_no; $i++){
  $RQ_ARGS->room_no = $i;
  $ROOM = new Room($RQ_ARGS);
  $ROOM->log_mode = true;
  $ROOM->last_date = $ROOM->date;

  $USERS = new UserDataSet($RQ_ARGS);
  $SELF  = new User();

  $RQ_ARGS->reverse_log = false;
  file_put_contents("../log/{$RQ_ARGS->prefix}{$i}.html", GenerateOldLog());

  $RQ_ARGS->reverse_log = true;
  $ROOM = new Room($RQ_ARGS);
  $ROOM->log_mode = true;
  $ROOM->last_date = $ROOM->date;

  $USERS = new UserDataSet($RQ_ARGS);
  $SELF  = new User();
  file_put_contents("../log/{$RQ_ARGS->prefix}{$i}r.html", GenerateOldLog());
  if($room_delete) DeleteRoom($i);
}
if($room_delete) OptimizeTable();

OutputActionResult('ログ生成',
		   $RQ_ARGS->min_room_no . ' 番地から' .
		   $RQ_ARGS->max_room_no . '番地までをHTML化しました');
