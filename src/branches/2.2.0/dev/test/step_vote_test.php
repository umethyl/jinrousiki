<?php
error_reporting(E_ALL);
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('test_class', 'image_class');

//-- 仮想村データをセット --//
Loader::LoadRequest('RequestBaseGame', true);

DevRoom::Initialize(array('name' => '投票テスト村', 'status' => 'playing'));
RQ::AddTestRoom('game_option', 'not_open_cast');
RQ::AddTestRoom('game_option', 'open_vote death_note');
RQ::AddTestRoom('game_option', 'chaosfull');
RQ::AddTestRoom('game_option', 'chaos_open_cast');
RQ::AddTestRoom('game_option', 'no_sub_role');
RQ::AddTestRoom('game_option', 'joker');
RQ::AddTestRoom('game_option', 'weather');
#RQ::AddTestRoom('game_option', 'seal_message');
#RQ::AddTestRoom('game_option', 'quiz');

DevUser::Initialize(25);
RQ::GetTest()->test_users[1]->role = 'resurrect_mania';
RQ::GetTest()->test_users[1]->live = 'dead';

RQ::GetTest()->test_users[2]->role = 'step_wolf authority';
RQ::GetTest()->test_users[2]->live = 'live';

RQ::GetTest()->test_users[3]->role = 'possessed_wolf possessed_target[3-17]';
RQ::GetTest()->test_users[3]->live = 'live';

RQ::GetTest()->test_users[4]->role = 'step_mage lovers[16] challenge_lovers rebel';
RQ::GetTest()->test_users[4]->live = 'live';

RQ::GetTest()->test_users[5]->role = 'soul_mage febris[6]';
RQ::GetTest()->test_users[5]->live = 'live';

RQ::GetTest()->test_users[6]->role = 'eclipse_medium mind_friend[21] possessed[4-15]';
RQ::GetTest()->test_users[6]->live = 'dead';

RQ::GetTest()->test_users[7]->role = 'step_guard lovers[16]';
RQ::GetTest()->test_users[7]->live = 'live';

RQ::GetTest()->test_users[8]->role = 'poison_guard';
RQ::GetTest()->test_users[8]->live = 'dead';

RQ::GetTest()->test_users[9]->role = 'missfire_cat joker[2]';
RQ::GetTest()->test_users[9]->live = 'live';

RQ::GetTest()->test_users[10]->role = 'ascetic_assassin death_note[5] speaker';
RQ::GetTest()->test_users[10]->live = 'live';

RQ::GetTest()->test_users[11]->role = 'fox downer_luck changed_vindictive';
RQ::GetTest()->test_users[11]->live = 'live';

RQ::GetTest()->test_users[12]->role = 'anti_voodoo death_selected[5]';
RQ::GetTest()->test_users[12]->live = 'live';

RQ::GetTest()->test_users[13]->role = 'step_mad';
RQ::GetTest()->test_users[13]->live = 'live';

RQ::GetTest()->test_users[14]->role = 'purple_fox death_warrant[6]';
RQ::GetTest()->test_users[14]->live = 'live';

RQ::GetTest()->test_users[15]->role = 'possessed_fox possessed_target[4-6] lost_ability';
RQ::GetTest()->test_users[15]->live = 'live';

RQ::GetTest()->test_users[16]->role = 'sweet_fairy lovers[16] challenge_lovers';
RQ::GetTest()->test_users[16]->live = 'live';

RQ::GetTest()->test_users[17]->role = 'psycho_necromancer possessed[3-3] disfavor';
RQ::GetTest()->test_users[17]->live = 'dead';

RQ::GetTest()->test_users[18]->role = 'step_mage';
RQ::GetTest()->test_users[18]->live = 'live';

RQ::GetTest()->test_users[19]->role = 'shadow_fairy psycho_infected';
RQ::GetTest()->test_users[19]->live = 'live';

RQ::GetTest()->test_users[20]->role = 'passion_vampire';
RQ::GetTest()->test_users[20]->live = 'live';

RQ::GetTest()->test_users[21]->role = 'revive_mania[6] mind_friend[21]';
RQ::GetTest()->test_users[21]->live = 'live';

RQ::GetTest()->test_users[22]->role = 'divine_escaper reduce_voter';
RQ::GetTest()->test_users[22]->live = 'live';

RQ::GetTest()->test_users[23]->role = 'mad deep_sleep';
RQ::GetTest()->test_users[23]->live = 'live';

RQ::GetTest()->test_users[24]->role = 'barrier_wizard infected[20]';
RQ::GetTest()->test_users[24]->live = 'live';

RQ::GetTest()->test_users[25]->role = 'vajra_yaksa disfavor';
RQ::GetTest()->test_users[25]->live = 'live';
RQ::GetTest()->test_users[25]->profile = "あーうー\nうーあー";

//RQ::GetTest()->test_users = 25;
DevUser::Complement();

//-- 仮想投票データをセット --//
$set_date = 6;
RQ::GetTest()->vote = new StdClass();
RQ::GetTest()->vote->day = array();
RQ::GetTest()->vote_target_day = array(
);
//決選投票用
/*
RQ::GetTest()->vote_target_day = array(
);
*/
if ($set_date == 1) { //初日用
  RQ::GetTest()->vote->night = array(
  );
} else {
  RQ::GetTest()->vote->night = array(
  );
}

//-- 仮想システムメッセージをセット --//
RQ::GetTest()->winner = 'wolf';
RQ::GetTest()->system_message = array(
  //-- 仮想イベントをセット --//
  4 => array(#'EVENT'   => array('blinder'),
	     #'VOTE_DUEL' => array(8),
	     'WEATHER' => array(44),
	     ),
  8 => array('WEATHER' => array(33)
	     )
);

//-- 仮想発言をセット --//
RQ::Set('say', '');
RQ::Set('font_type', 'normal');

//-- データ収集 --//
//DB::Connect(); //DB接続 (必要なときだけ設定する)
DevRoom::Load();
DB::$ROOM->date = $set_date;
#DB::$ROOM->scene = 'beforegame';
#DB::$ROOM->scene = 'day';
DB::$ROOM->scene = 'night';
#DB::$ROOM->scene = 'aftergame';
//DB::$ROOM->system_time = Time::Get(); //現在時刻を取得

DevUser::Load();
if (DB::$ROOM->IsDate(1)) {
  foreach (DB::$USER->rows as $user) $user->live = 'live'; //初日用
}
#DB::$USER->ByID(9)->live = 'live';
DB::$SELF = DB::$USER->ByID(18);
#DB::$SELF = DB::$USER->TraceExchange(14);
foreach (DB::$USER->rows as $user) {
  if (! isset($user->target_no)) $user->target_no = 0;
}

//-- データ出力 --//
StepVoteTest::Output();
