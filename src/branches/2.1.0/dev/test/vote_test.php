<?php
error_reporting(E_ALL);
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
Loader::LoadFile('test_class', 'image_class');

//-- 特殊モード設定 --//
$vote_view_mode = false; //投票表示モード
$talk_view_mode = false; //発言表示モード
$role_view_mode = false; //画像表示モード
$cast_view_mode = false; //配役情報表示モード

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

RQ::GetTest()->test_users[2]->role = 'silver_wolf authority';
RQ::GetTest()->test_users[2]->live = 'live';

RQ::GetTest()->test_users[3]->role = 'possessed_wolf possessed_target[3-17]';
RQ::GetTest()->test_users[3]->live = 'live';

RQ::GetTest()->test_users[4]->role = 'stargazer_mage lovers[16] challenge_lovers rebel';
RQ::GetTest()->test_users[4]->live = 'live';

RQ::GetTest()->test_users[5]->role = 'soul_mage febris[6]';
RQ::GetTest()->test_users[5]->live = 'live';

RQ::GetTest()->test_users[6]->role = 'eclipse_medium mind_friend[21] possessed[4-15]';
RQ::GetTest()->test_users[6]->live = 'dead';

RQ::GetTest()->test_users[7]->role = 'dummy_guard lovers[16]';
RQ::GetTest()->test_users[7]->live = 'live';

RQ::GetTest()->test_users[8]->role = 'poison_guard';
RQ::GetTest()->test_users[8]->live = 'dead';

RQ::GetTest()->test_users[9]->role = 'missfire_cat joker[2]';
RQ::GetTest()->test_users[9]->live = 'live';

RQ::GetTest()->test_users[10]->role = 'ascetic_assassin death_note[5]';
RQ::GetTest()->test_users[10]->live = 'live';

RQ::GetTest()->test_users[11]->role = 'fox downer_luck changed_vindictive';
RQ::GetTest()->test_users[11]->live = 'live';

RQ::GetTest()->test_users[12]->role = 'anti_voodoo death_selected[5]';
RQ::GetTest()->test_users[12]->live = 'live';

RQ::GetTest()->test_users[13]->role = 'possessed_mad';
RQ::GetTest()->test_users[13]->live = 'live';

RQ::GetTest()->test_users[14]->role = 'purple_fox death_warrant[6]';
RQ::GetTest()->test_users[14]->live = 'live';

RQ::GetTest()->test_users[15]->role = 'possessed_fox possessed_target[4-6] lost_ability';
RQ::GetTest()->test_users[15]->live = 'live';

RQ::GetTest()->test_users[16]->role = 'sweet_fairy lovers[16] challenge_lovers';
RQ::GetTest()->test_users[16]->live = 'live';

RQ::GetTest()->test_users[17]->role = 'psycho_necromancer possessed[3-3] disfavor';
RQ::GetTest()->test_users[17]->live = 'dead';

RQ::GetTest()->test_users[18]->role = 'guide_poison no_last_words';
RQ::GetTest()->test_users[18]->live = 'live';

RQ::GetTest()->test_users[19]->role = 'shadow_fairy psycho_infected';
RQ::GetTest()->test_users[19]->live = 'live';

RQ::GetTest()->test_users[20]->role = 'passion_vampire';
RQ::GetTest()->test_users[20]->live = 'live';

RQ::GetTest()->test_users[21]->role = 'revive_mania[6] mind_friend[21]';
RQ::GetTest()->test_users[21]->live = 'live';

RQ::GetTest()->test_users[22]->role = 'divine_escaper reduce_voter';
RQ::GetTest()->test_users[22]->live = 'live';

RQ::GetTest()->test_users[23]->role = 'revive_mad deep_sleep';
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
  array('id' =>  2, 'target_no' => 13),
  array('id' =>  3, 'target_no' =>  7),
  //array('id' =>  3, 'target_no' => 10),
  array('id' =>  4, 'target_no' => 11),
  array('id' =>  5, 'target_no' => 25),
  //array('id' =>  6, 'target_no' =>  3),
  //array('id' =>  7, 'target_no' =>  3),
  array('id' =>  7, 'target_no' => 14),
  array('id' =>  8, 'target_no' =>  9),
  array('id' =>  9, 'target_no' => 20),
  array('id' => 10, 'target_no' => 20),
  array('id' => 11, 'target_no' => 20),
  array('id' => 12, 'target_no' => 20),
  array('id' => 13, 'target_no' =>  3),
  array('id' => 14, 'target_no' => 23),
  array('id' => 15, 'target_no' =>  7),
  array('id' => 16, 'target_no' => 11),
  //array('id' => 17, 'target_no' => 22),
  array('id' => 18, 'target_no' => 16),
  //array('id' => 18, 'target_no' => 3),
  array('id' => 19, 'target_no' => 22),
  array('id' => 20, 'target_no' => 22),
  array('id' => 21, 'target_no' => 18),
  array('id' => 22, 'target_no' => 25),
  array('id' => 23, 'target_no' => 14),
  array('id' => 24, 'target_no' => 25),
  //array('id' => 25, 'target_no' =>  3),
  array('id' => 25, 'target_no' => 12),
);
//決選投票用
/*
RQ::GetTest()->vote_target_day = array(
  array('id' =>  2, 'target_no' => 4),
  array('id' =>  3, 'target_no' => 5),
  //array('id' =>  3, 'target_no' => 10),
  array('id' =>  4, 'target_no' => 5),
  array('id' =>  5, 'target_no' => 4),
  //array('id' =>  6, 'target_no' =>  3),
  //array('id' =>  7, 'target_no' =>  3),
  array('id' =>  7, 'target_no' => 5),
  //array('id' =>  8, 'target_no' =>  9),
  array('id' =>  9, 'target_no' => 4),
  array('id' => 10, 'target_no' => 4),
  array('id' => 11, 'target_no' => 4),
  array('id' => 12, 'target_no' => 4),
  array('id' => 13, 'target_no' => 4),
  array('id' => 14, 'target_no' => 4),
  array('id' => 15, 'target_no' => 4),
  array('id' => 16, 'target_no' => 4),
  //array('id' => 17, 'target_no' => 22),
  array('id' => 18, 'target_no' => 5),
  //array('id' => 18, 'target_no' => 3),
  array('id' => 19, 'target_no' => 5),
  array('id' => 20, 'target_no' => 5),
  array('id' => 21, 'target_no' => 5),
  array('id' => 22, 'target_no' => 5),
  array('id' => 23, 'target_no' => 5),
  array('id' => 24, 'target_no' => 5),
  //array('id' => 25, 'target_no' =>  3),
  array('id' => 25, 'target_no' => 4),
);
*/
if ($set_date == 1) { //初日用
  RQ::GetTest()->vote->night = array(
    array('user_no' =>  2,	'target_no' =>  1,	'type' => 'WOLF_EAT'),
    array('user_no' =>  4,	'target_no' => 14,	'type' => 'MAGE_DO'),
    array('user_no' =>  5,	'target_no' => 14,	'type' => 'MAGE_DO'),
    #array('user_no' => 11,	'target_no' =>  4,	'type' => 'VOODOO_MAD_DO'),
    #array('user_no' => 13,	'target_no' => 18,	'type' => 'MAGE_DO'),
    #array('user_no' => 14,	'target_no' =>  4,	'type' => 'CHILD_FOX_DO'),
    array('user_no' => 14,	'target_no' => '2 5',	'type' => 'CUPID_DO'),
    array('user_no' => 16,	'target_no' => '16 18',	'type' => 'CUPID_DO'),
    array('user_no' => 19,	'target_no' => 20,	'type' => 'FAIRY_DO'),
    #array('user_no' => 21,	'target_no' => '18 21',	'type' => 'CUPID_DO'),
    array('user_no' => 21,	'target_no' => 11,	'type' => 'MANIA_DO'),
    array('user_no' => 22,	'target_no' => 24,	'type' => 'DUELIST_DO'),
    #array('user_no' => 23,	'target_no' =>  4,	'type' => 'MANIA_DO'),
    #array('user_no' => 23,	'target_no' =>  4,	'type' => 'CHILD_FOX_DO'),
    #array('user_no' => 24,	'target_no' =>  2,	'type' => 'MIND_SCANNER_DO')
  );
} else {
  RQ::GetTest()->vote->night = array(
    array('user_no' => 2, 	'target_no' => 21,	'type' => 'WOLF_EAT'),
    #array('user_no' => 3, 	'target_no' => 12,	'type' => 'WOLF_EAT'),
    array('user_no' => 4, 	'target_no' => 3,	'type' => 'MAGE_DO'),
    array('user_no' => 5, 	'target_no' => 13,	'type' => 'MAGE_DO'),
    array('user_no' => 7, 	'target_no' => 11,	'type' => 'GUARD_DO'),
    #array('user_no' => 8, 	'target_no' => 15,	'type' => 'GUARD_DO'),
    #array('user_no' => 8, 	'target_no' => 3,	'type' => 'ANTI_VOODOO_DO'),
    array('user_no' => 9, 	'target_no' => 3,	'type' => 'POISON_CAT_DO'),
    #array('user_no' => 9, 	'target_no' => null,	'type' => 'POISON_CAT_NOT_DO'),
    array('user_no' => 10, 	'target_no' => 11,	'type' => 'ASSASSIN_DO'),
    #array('user_no' => 10, 	'target_no' => null,	'type' => 'ASSASSIN_NOT_DO'),
    #array('user_no' => 10, 	'target_no' => 8,	'type' => 'DEATH_NOTE_DO'),
    #array('user_no' => 10, 	'target_no' => null,	'type' => 'DEATH_NOTE_NOT_DO'),
    #array('user_no' => 11, 	'target_no' => 16,	'type' => 'JAMMER_MAD_DO'),
    #array('user_no' => 11, 	'target_no' => 4,	'type' => 'VOODOO_MAD_DO'),
    #array('user_no' => 11, 	'target_no' => 4,	'type' => 'VOODOO_FOX_DO'),
    #array('user_no' => 11, 	'type' => 'DREAM_EAT',	'target_no' => 14),
    #array('user_no' => 12, 	'type' => 'TRAP_MAD_DO',	'target_no' => 16),
    #array('user_no' => 12, 	'type' => 'TRAP_MAD_NOT_DO',	'target_no' => null),
    #array('user_no' => 12, 	'type' => 'POSSESSED_DO',	'target_no' => 23),
    #array('user_no' => 12, 	'type' => 'POSSESSED_NOT_DO',	'target_no' => null),
    array('user_no' => 12, 	'type' => 'ANTI_VOODOO_DO',	'target_no' => 4),
    #array('user_no' => 12, 	'type' => 'MAGE_DO', 'target_no' => 16),
    #array('user_no' => 12, 	'type' => 'WOLF_EAT', 'target_no' => 2),
    #array('user_no' => 12, 	'type' => 'VOODOO_FOX_DO',	'target_no' => 21),
    array('user_no' => 13, 	'type' => 'POSSESSED_DO',	'target_no' => 8),
    #array('user_no' => 13, 	'type' => 'POSSESSED_NOT_DO',	'target_no' => null),
    #array('user_no' => 13, 	'type' => 'POISON_CAT_DO',	'target_no' => 6),
    #array('user_no' => 13, 	'type' => 'POISON_CAT_NOT_DO',	'target_no' => null),
    #array('user_no' => 13, 	'type' => 'TRAP_MAD_DO',	'target_no' => 13),
    #array('user_no' => 13, 	'type' => 'TRAP_MAD_NOT_DO',	'target_no' => null),
    #array('user_no' => 13, 	'type' => 'VOODOO_KILLER_DO',	'target_no' =>  7),
    #array('user_no' => 14, 	'type' => 'CHILD_FOX_DO',	'target_no' => 18),
    #array('user_no' => 14, 	'type' => 'VOODOO_KILLER_DO',	'target_no' => 10),
    #array('user_no' => 14, 	'type' => 'JAMMER_MAD_DO',	'target_no' => 5),
    #array('user_no' => 17, 	'type' => 'FAIRY_DO', 'target_no' => 22),
    #array('user_no' => 18, 	'type' => 'VOODOO_FOX_DO', 'target_no' => 20),
    array('user_no' => 19, 	'type' => 'FAIRY_DO', 'target_no' => 18),
    array('user_no' => 20, 	'type' => 'VAMPIRE_DO', 'target_no' => 23),
    #array('user_no' => 21, 	'type' => 'CHILD_FOX_DO',	'target_no' => 5),
    array('user_no' => 22, 	'type' => 'ESCAPE_DO', 'target_no' => 12),
    #array('user_no' => 22, 	'type' => 'FAIRY_DO', 'target_no' => 13),
    #array('user_no' => 22, 	'type' => 'TRAP_MAD_DO', 'target_no' => 22),
    #array('user_no' => 22, 	'type' => 'OGRE_DO', 'target_no' => 24),
    #array('user_no' => 22, 	'type' => 'OGRE_NOT_DO', 'target_no' => null),
    #array('user_no' => 22, 	'type' => 'WIZARD_DO', 'target_no' => 23),
    #array('user_no' => 23, 	'type' => 'REPORTER_DO', 'target_no' => 12),
    #array('user_no' => 23, 	'type' => 'ESCAPE_DO', 'target_no' => 11),
    #array('user_no' => 23, 	'type' => 'REPORTER_DO', 'target_no' => 13),
    #array('user_no' => 23, 	'type' => 'ASSASSIN_DO', 'target_no' => 3),
    #array('user_no' => 23, 	'type' => 'MIND_SCANNER_DO', 'target_no' => 24),
    #array('user_no' => 23, 	'type' => 'VAMPIRE_DO', 'target_no' => 16),
    #array('user_no' => 24, 	'type' => 'MIND_SCANNER_DO', 'target_no' => 2),
    #array('user_no' => 24, 	'type' => 'WIZARD_DO', 'target_no' => 11),
    array('user_no' => 24, 	'type' => 'SPREAD_WIZARD_DO', 'target_no' => '12 13'),
    #array('user_no' => 24, 	'type' => 'SPREAD_WIZARD_DO', 'target_no' => 12),
    #array('user_no' => 25, 	'type' => 'TRAP_MAD_DO', 'target_no' => 22),
    #array('user_no' => 25, 	'type' => 'OGRE_DO', 'target_no' => 9),
    array('user_no' => 25, 	'type' => 'OGRE_NOT_DO', 'target_no' => null),
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
RQ::$get->say = '';
#RQ::$get->say = "占いCO！\n赤は村人！今日は木曜日ですよwww？";
RQ::$get->font_type = 'weak'; 'normal';

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
if (DB::$ROOM->date == 1) {
  foreach (DB::$USER->rows as $user) $user->live = 'live'; //初日用
}
#DB::$USER->ByID(9)->live = 'live';
#DB::$SELF = DB::$USER->ByID(9);
#DB::$SELF = DB::$USER->TraceExchange(14);
foreach (DB::$USER->rows as $user) {
  if (! isset($user->target_no)) $user->target_no = 0;
}

//-- データ出力 --//
if ($vote_view_mode) VoteTest::OutputVote(); //投票表示モード
if ($role_view_mode) { //画像表示モード
  HTML::OutputHeader('投票テスト', 'game_play', true); //HTMLヘッダ
  #foreach (array_keys(RoleData::$main_role_list) as $role) Image::Role()->Output($role);
  #foreach (array_keys(RoleData::$sub_role_list)  as $role) Image::Role()->Output($role);
  #foreach (array_keys(RoleData::$main_role_list) as $role) Image::Role()->Output('result_'.$role);
  $header = 'prediction_weather_';
  #foreach (RoleData::$weather_list as $stack) Image::Role()->Output($header.$stack['event']);
  HTML::OutputFooter(true);
}
if ($cast_view_mode) VoteTest::OutputCast(); //配役情報表示モード
if ($talk_view_mode) { //発言表示モード
  Loader::LoadFile('talk_class');

  HTML::OutputHeader('投票テスト', 'game_play');
  echo DB::$ROOM->GenerateCSS();
  HTML::OutputBodyHeader();
  RQ::$get->add_role = false;
  RQ::GetTest()->talk_data = new StdClass();
  //昼の発言
  $stack = array(
    array('uname' => 'moon',
	  'font_type' => 'normal', 'sentence' => '●かー'),
    array('uname' => 'light_blue',
	  'font_type' => 'weak', 'sentence' => 'えっ'),
    array('uname' => 'green',
	  'location' => 'system', 'action' => 'OBJECTION'),
    array('uname' => 'dark_gray',
	  'font_type' => 'weak', 'sentence' => 'チラッ'),
    array('uname' => 'yellow',
	  'font_type' => 'strong', 'sentence' => "占いCO\n黒は●"),
    array('uname' => 'light_gray',
	  'font_type' => 'normal', 'sentence' => 'おはよう'),
    array('uname' => 'system',
	  'location' => 'system', 'action' => 'MORNING', 'sentence' => DB::$ROOM->date),
  );
  foreach ($stack as &$list) {
    $list['scene'] = 'day';
  }
  RQ::GetTest()->talk_data->day = $stack;

  $stack = array(
    array('uname' => 'cloud',
	  'font_type' => 'normal', 'sentence' => '吸血鬼なんだ'),
    array('uname' => 'light_blue',
	  'font_type' => 'weak', 'sentence' => 'えっ'),
    array('uname' => 'moon',
	  'font_type' => 'normal', 'sentence' => 'あーうー'),
    array('uname' => 'gold',
	  'location' => 'common', 'font_type' => 'normal',
	  'sentence' => 'やあやあ'),
    array('uname' => 'rose',
	  'font_type' => 'strong', 'sentence' => '誰吸血しようかな'),
    array('uname' => 'frame',
	  'font_type' => 'normal', 'sentence' => 'どうしよう'),
    array('uname' => 'black',
	  'location' => 'fox', 'font_type' => 'weak',
	  'sentence' => '占い師早く死んで欲しいなぁ'),
    array('uname' => 'cherry',
	  'location' => 'mad', 'font_type' => 'weak',
	  'sentence' => 'やあ'),
    array('uname' => 'green',
	  'font_type' => 'normal', 'sentence' => 'てすてす'),
    array('uname' => 'dark_gray',
	  'font_type' => 'weak', 'sentence' => 'チラッ'),
    array('uname' => 'yellow',
	  'font_type' => 'strong', 'sentence' => "占いCO\n黒は●"),
    array('uname' => 'light_gray',
	  'location' => 'wolf', 'font_type' => 'normal',
	  'sentence' => '生き延びたか'),
    array('uname' => 'system', 'action' => 'NIGHT')
  );
  foreach ($stack as &$list) {
    $list['scene'] = 'night';
    if (! isset($list['location'])) {
      $list['location'] = $list['uname'] == 'system' ? 'system' : 'self_talk';
    }
  }
  RQ::GetTest()->talk_data->night = $stack;

  RQ::GetTest()->talk = array();
  foreach (RQ::GetTest()->talk_data->{DB::$ROOM->scene} as $stack) {
    RQ::GetTest()->talk[] = new TalkParser($stack);
  }
  //Text::p(RQ::GetTest()->talk);
  GameHTML::OutputPlayer();
  if (DB::$SELF->user_no > 0) RoleHTML::OutputAbility();
  Talk::Output();
  HTML::OutputFooter(true);
}

HTML::OutputHeader('投票テスト', 'game_play', true);
GameHTML::OutputPlayer();
RoleHTML::OutputAbility();
if (RQ::$get->say != '') { //発言変換テスト
  RoleTalk::Convert(RQ::$get->say);
  RoleTalk::Save(RQ::$get->say, 'day', 0);
}
if (DB::$ROOM->IsDay()) { //昼の投票テスト
  $self_id = DB::$SELF->user_no;
  RQ::$get->situation = 'VOTE_KILL';
  RQ::$get->back_url = '';
  foreach (RQ::GetTest()->vote_target_day as $stack) {
    DB::$SELF = DB::$USER->ByID($stack['id']);
    RQ::$get->target_no = $stack['target_no'];
    Vote::VoteDay();
  }
  $vote_message_list = Vote::AggregateDay();
  if (! is_array($vote_message_list)) $vote_message_list = array();
  $stack = array();
  foreach ($vote_message_list as $uname => $vote_data) {
    $vote_data['handle_name'] = DB::$USER->GetHandleName($uname);
    $vote_data['count'] = DB::$ROOM->revote_count + 1;
    $stack[] = $vote_data;
  }
  echo GameHTML::ParseVote($stack, DB::$ROOM->date);
  DB::$ROOM->date++;
  DB::$ROOM->log_mode = false; //イベント確認用
  DB::$ROOM->scene = 'day'; //イベント確認用
  //DB::$ROOM->scene = 'night';
  DB::$SELF = DB::$USER->ByID($self_id);
}
elseif (DB::$ROOM->IsNight()) { // 夜の投票テスト
  //Text::p(RQ::GetTest()->vote->night);
  Vote::AggregateNight();
}
elseif (DB::$ROOM->IsAfterGame()) { //勝敗判定表示
  Loader::LoadFile('winner_message');
  DB::$ROOM->log_mode = false;
  DB::$ROOM->personal_mode = false;
  Winner::Output();
  HTML::OutputFooter();
}
//Text::p(RQ::GetTest()->system_message, 'System');
//Text::p(RQ::GetTest()->result_ability, 'Ability');
//Text::p(RQ::GetTest()->result_dead, 'Dead');

do {
  //break;
  foreach (DB::$USER->rows as $user) {
    unset($user->virtual_role);
    $user->live = $user->IsLive(true) ? 'live' : 'dead';
    $user->Reparse();
    $user->target_no = 0;
  }

  foreach (RQ::GetTest()->vote->night as $stack) {
    //$uname = DB::$USER->GetHandleName($stack['uname'], true);
    $uname = DB::$USER->ByVirtual($stack['user_no'])->handle_name;
    switch($stack['type']) {
    case 'CUPID_DO':
      $target_stack = array();
      foreach (explode(' ', $stack['target_no']) as $id) {
	$user = DB::$USER->ByVirtual($id);
	$target_stack[$user->user_no] = $user->handle_name;
      }
      $target_uname = implode(' ', $target_stack);
      break;

    case 'SPREAD_WIZARD_DO':
      $target_stack = array();
      foreach (explode(' ', $stack['target_no']) as $id) {
	$user = DB::$USER->ByVirtual($id);
	$target_stack[$user->user_no] = $user->handle_name;
      }
      ksort($target_stack);
      $target_uname = implode(' ', $target_stack);
      break;

    default:
      //$target_uname = DB::$USER->GetHandleName($stack['target_uname'], true);
      if (isset($stack['target_no'])) {
	$target_uname = DB::$USER->ByVirtual($stack['target_no'])->handle_name;
      }
      break;
    }
    $stack_list[] = array('type' => $stack['type'],
			  'message' =>  $uname . "\t" . $target_uname);
    RQ::GetTest()->ability_action_list = $stack_list;
  }
  GameHTML::OutputAbilityAction();

  //Text::p(RQ::GetTest()->system_message, 'SystemMessage');
  DB::$ROOM->LoadEvent();
  DB::$USER->SetEvent();
  //Text::p(DB::$ROOM->event);
  GameHTML::OutputDead();

  //DB::$ROOM->status = 'finished';
  GameHTML::OutputPlayer();
  RoleHTML::OutputAbility();
  //foreach (array(5, 18, 2, 9, 13, 14, 23) as $id) {
  foreach (range(1, 25) as $id) {
    DB::$SELF = DB::$USER->ByID($id); RoleHTML::OutputAbility();
  }
  //var_dump(DB::$USER->IsOpenCast());
} while(false);
//Text::p(Lottery::ToProbability(GameConfig::$weather_list));
//InsertLog();
//Text::p(RoleManager::$file);
//Text::p(array_keys(RoleManager::$class));
//Text::p(DB::$USER->role);
//Text::p(Loader::$file);
HTML::OutputFooter();
