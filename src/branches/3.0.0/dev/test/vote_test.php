<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
require_once('init.php');
Loader::LoadFile('test_class', 'image_class');
Loader::LoadRequest('RequestBaseGame', true);
//$PAPARAZZI = new Paparazzi(); $PAPARAZZI->written = false; shot(1);

//-- 特殊モード設定 --//
$vote_view_mode = false; //投票表示モード
$cast_view_mode = false; //配役情報表示モード
$talk_view_mode = false; //発言表示モード
$role_view_mode = false; //画像表示モード
$role_view_list = array(
  'main'    => true, #false,
  'sub'     => false,
  'result'  => false,
  'weather' => false
);

//-- 仮想村データをセット --//
DevRoom::Initialize(array('name' => '投票テスト村', 'status' => RoomStatus::PLAYING));
RQ::AddTestRoom('game_option', 'not_open_cast');
RQ::AddTestRoom('game_option', 'open_vote death_note');
RQ::AddTestRoom('game_option', 'chaosfull');
RQ::AddTestRoom('game_option', 'chaos_open_cast');
RQ::AddTestRoom('game_option', 'no_sub_role');
RQ::AddTestRoom('game_option', 'joker');
RQ::AddTestRoom('game_option', 'weather');
#RQ::AddTestRoom('game_option', 'full_weather');
#RQ::AddTestRoom('game_option', 'seal_message');
#RQ::AddTestRoom('game_option', 'quiz');
RQ::AddTestRoom('game_option', 'no_silence');

DevUser::Initialize(30);
RQ::GetTest()->test_users[1]->role = 'harp_mania';
RQ::GetTest()->test_users[1]->live = UserLive::DEAD;

RQ::GetTest()->test_users[2]->role = 'cursed_wolf authority';
RQ::GetTest()->test_users[2]->live = UserLive::LIVE;

RQ::GetTest()->test_users[3]->role = 'possessed_wolf possessed_target[3-17]';
RQ::GetTest()->test_users[3]->live = UserLive::LIVE;

RQ::GetTest()->test_users[4]->role = 'soul_mage lovers[16] challenge_lovers rebel';
RQ::GetTest()->test_users[4]->live = UserLive::LIVE;

RQ::GetTest()->test_users[5]->role = 'stargazer_mage febris[6]';
RQ::GetTest()->test_users[5]->live = UserLive::LIVE;

RQ::GetTest()->test_users[6]->role = 'eclipse_medium possessed[4-15] frostbite[7]';
RQ::GetTest()->test_users[6]->live = UserLive::DEAD;

RQ::GetTest()->test_users[7]->role = 'doll lovers[13] mind_friend[13] vega_lovers';
RQ::GetTest()->test_users[7]->live = UserLive::LIVE;

RQ::GetTest()->test_users[8]->role = 'anti_voodoo';
RQ::GetTest()->test_users[8]->live = UserLive::LIVE;

RQ::GetTest()->test_users[9]->role = 'revive_fox joker[2] lovers[14] letter_exchange[4]';
RQ::GetTest()->test_users[9]->live = UserLive::LIVE;

RQ::GetTest()->test_users[10]->role = 'step_assassin death_note[5] speaker';
RQ::GetTest()->test_users[10]->live = UserLive::LIVE;

RQ::GetTest()->test_users[11]->role = 'sacrifice_depraver downer_luck';
RQ::GetTest()->test_users[11]->live = UserLive::LIVE;

RQ::GetTest()->test_users[12]->role = 'weather_priest death_selected[9] sweet_status[16]';
RQ::GetTest()->test_users[12]->live = UserLive::LIVE;

RQ::GetTest()->test_users[13]->role = 'altair_cupid lady mind_friend[13] lovers[13]';
RQ::GetTest()->test_users[13]->live = UserLive::LIVE;

RQ::GetTest()->test_users[14]->role = 'letter_cupid lovers[14] letter_exchange[1]';
RQ::GetTest()->test_users[14]->live = UserLive::LIVE;

RQ::GetTest()->test_users[15]->role = 'possessed_mad possessed_target[4-6] lost_ability';
RQ::GetTest()->test_users[15]->live = UserLive::LIVE;

RQ::GetTest()->test_users[16]->role = 'moon_cupid lovers[16] challenge_lovers mind_receiver[4]';
RQ::GetTest()->test_users[16]->live = UserLive::LIVE;

RQ::GetTest()->test_users[17]->role = 'critical_common possessed[3-3] disfavor';
RQ::GetTest()->test_users[17]->live = UserLive::DEAD;

RQ::GetTest()->test_users[18]->role = 'attempt_necromancer no_last_words rival[22]';
RQ::GetTest()->test_users[18]->live = UserLive::LIVE;

RQ::GetTest()->test_users[19]->role = 'barrier_wizard psycho_infected fake_lovers[14] sweet_status[16]';
RQ::GetTest()->test_users[19]->live = UserLive::LIVE;

RQ::GetTest()->test_users[20]->role = 'doom_vampire downer_luck rival[22]';
RQ::GetTest()->test_users[20]->live = UserLive::LIVE;

RQ::GetTest()->test_users[21]->role = 'soul_mania[13] mind_friend[21]';
RQ::GetTest()->test_users[21]->live = UserLive::LIVE;

RQ::GetTest()->test_users[22]->role = 'enchant_mad';
RQ::GetTest()->test_users[22]->live = UserLive::LIVE;

RQ::GetTest()->test_users[23]->role = 'meteor_tengu earplug death_selected[8]';
RQ::GetTest()->test_users[23]->live = UserLive::LIVE;

RQ::GetTest()->test_users[24]->role = 'eye_wolf infected[20]';
RQ::GetTest()->test_users[24]->live = UserLive::LIVE;

RQ::GetTest()->test_users[25]->role = 'cursed_yaksa disfavor';
RQ::GetTest()->test_users[25]->live = UserLive::LIVE;
RQ::GetTest()->test_users[25]->profile = "あーうー\nうーあー";

RQ::GetTest()->test_users[26]->role = 'trick_mania';
RQ::GetTest()->test_users[26]->live = UserLive::LIVE;

RQ::GetTest()->test_users[27]->role = 'dummy_mania[23]';
RQ::GetTest()->test_users[27]->live = UserLive::LIVE;

RQ::GetTest()->test_users[28]->role = 'necromancer bad_luck';
RQ::GetTest()->test_users[28]->live = UserLive::LIVE;

RQ::GetTest()->test_users[29]->role = 'priest_tengu';
RQ::GetTest()->test_users[29]->live = UserLive::LIVE;

RQ::GetTest()->test_users[30]->role = 'weather_priest';
RQ::GetTest()->test_users[30]->live = UserLive::LIVE;

//RQ::GetTest()->test_users = 25;
DevUser::Complement();

//-- 仮想投票データをセット --//
$set_date = 5;
RQ::GetTest()->vote = new StdClass();
RQ::GetTest()->vote->day = array();
RQ::GetTest()->vote_target_day = array(
  array('id' =>  2, 'target_no' => 13),
  array('id' =>  3, 'target_no' =>  7),
  //array('id' =>  3, 'target_no' => 10),
  array('id' =>  4, 'target_no' => 11),
  //array('id' =>  4, 'target_no' => 13),
  array('id' =>  5, 'target_no' => 25),
  //array('id' =>  6, 'target_no' =>  3),
  //array('id' =>  7, 'target_no' =>  3),
  array('id' =>  7, 'target_no' => 14),
  array('id' =>  8, 'target_no' =>  9),
  array('id' =>  9, 'target_no' => 29),
  array('id' => 10, 'target_no' => 20),
  array('id' => 11, 'target_no' => 22),
  array('id' => 12, 'target_no' => 22),
  array('id' => 13, 'target_no' =>  4),
  array('id' => 14, 'target_no' => 23),
  array('id' => 15, 'target_no' =>  7),
  array('id' => 16, 'target_no' => 20),
  //array('id' => 17, 'target_no' => 22),
  array('id' => 18, 'target_no' => 23),
  //array('id' => 18, 'target_no' => 3),
  array('id' => 19, 'target_no' => 22),
  array('id' => 20, 'target_no' => 22),
  array('id' => 21, 'target_no' => 18),
  array('id' => 22, 'target_no' => 18),
  array('id' => 23, 'target_no' => 29),
  array('id' => 24, 'target_no' => 22),
  //array('id' => 25, 'target_no' =>  3),
  array('id' => 25, 'target_no' => 29),
  array('id' => 26, 'target_no' => 12),
  array('id' => 27, 'target_no' =>  3),
  array('id' => 28, 'target_no' => 29),
  array('id' => 29, 'target_no' => 11),
  array('id' => 30, 'target_no' => 26),
);
//決選投票用
/*
RQ::GetTest()->vote_target_day = array(
  array('id' =>  2, 'target_no' => 14),
  array('id' =>  3, 'target_no' => 14),
  array('id' =>  4, 'target_no' => 14),
  array('id' =>  5, 'target_no' => 14),
  array('id' =>  7, 'target_no' => 14),
  array('id' =>  8, 'target_no' => 14),
  array('id' =>  9, 'target_no' => 14),
  array('id' => 10, 'target_no' => 14),
  array('id' => 11, 'target_no' => 14),
  array('id' => 12, 'target_no' => 14),
  array('id' => 13, 'target_no' => 14),
  array('id' => 14, 'target_no' => 15),
  array('id' => 15, 'target_no' => 14),
  array('id' => 16, 'target_no' => 15),
  array('id' => 18, 'target_no' => 15),
  array('id' => 19, 'target_no' => 15),
  array('id' => 20, 'target_no' => 15),
  array('id' => 21, 'target_no' => 15),
  array('id' => 22, 'target_no' => 15),
  array('id' => 23, 'target_no' => 15),
  array('id' => 24, 'target_no' => 15),
  array('id' => 25, 'target_no' => 15),
  array('id' => 26, 'target_no' => 15),
  array('id' => 27, 'target_no' => 15),
  array('id' => 28, 'target_no' => 15),
  array('id' => 29, 'target_no' => 15),
  array('id' => 30, 'target_no' => 15),
);
*/
if ($set_date == 1) { //初日用
  RQ::GetTest()->vote->night = array(
    array('user_no' =>  2,	'target_no' =>  1,	'type' => 'WOLF_EAT'),
    array('user_no' =>  4,	'target_no' => 14,	'type' => 'MAGE_DO'),
    array('user_no' =>  5,	'target_no' => 12,	'type' => 'MAGE_DO'),
    //array('user_no' => 11,	'target_no' =>  4,	'type' => 'VOODOO_MAD_DO'),
    //array('user_no' => 13,	'target_no' => 18,	'type' => 'MAGE_DO'),
    array('user_no' => 13,	'target_no' => '7 13',	'type' => 'CUPID_DO'),
    //array('user_no' => 14,	'target_no' =>  4,	'type' => 'CHILD_FOX_DO'),
    array('user_no' => 14,	'target_no' => '2 5',	'type' => 'CUPID_DO'),
    array('user_no' => 16,	'target_no' => '4 16',	'type' => 'CUPID_DO'),
    array('user_no' => 17,	'target_no' => '12 19',	'type' => 'CUPID_DO'),
    //array('user_no' => 19,	'target_no' => 20,	'type' => 'FAIRY_DO'),
    array('user_no' => 21,	'target_no' => 11,	'type' => 'MANIA_DO'),
    //array('user_no' => 22,	'target_no' => 24,	'type' => 'DUELIST_DO'),
    array('user_no' => 22,	'target_no' => 28,	'type' => 'FAIRY_DO'),
    array('user_no' => 26,	'target_no' => 12,	'type' => 'MANIA_DO'),
    array('user_no' => 27,	'target_no' => 23,	'type' => 'MANIA_DO'),
    //array('user_no' => 23,	'target_no' =>  4,	'type' => 'CHILD_FOX_DO'),
    //array('user_no' => 24,	'target_no' =>  2,	'type' => 'MIND_SCANNER_DO')
  );
} else {
  RQ::GetTest()->vote->night = array(
    array('user_no' => 2,	'target_no' => 28,	'type' => 'WOLF_EAT'),
    //array('user_no' => 3,	'target_no' => 22,	'type' => 'WOLF_EAT'),
    array('user_no' => 4, 	'target_no' => 29,	'type' => 'MAGE_DO'),
    //array('user_no' => 4, 	'target_no' => '9 14 19 18 3',	'type' => 'STEP_MAGE_DO'),
    array('user_no' => 5,	'target_no' => 7,	'type' => 'MAGE_DO'),
    //array('user_no' => 7,	'target_no' => 8,	'type' => 'GUARD_DO'),
    //array('user_no' => 7,	'target_no' => '8 9 10',	'type' => 'STEP_GUARD_DO'),
    //array('user_no' => 8,	'target_no' => 16,	'type' => 'GUARD_DO'),
    array('user_no' => 8,	'target_no' =>  3,	'type' => 'ANTI_VOODOO_DO'),
    array('user_no' => 9,	'target_no' =>  3,	'type' => 'POISON_CAT_DO'),
    //array('user_no' => 9,	'target_no' => null,	'type' => 'POISON_CAT_NOT_DO'),
    //array('user_no' => 10,	'target_no' => 23,	'type' => 'ASSASSIN_DO'),
    //array('user_no' => 10,	'target_no' => null,	'type' => 'ASSASSIN_NOT_DO'),
    array('user_no' => 10,	'target_no' => '5 10 15 20 25',	'type' => 'STEP_ASSASSIN_DO'),
    //array('user_no' => 10,	'target_no' =>  8,	'type' => 'DEATH_NOTE_DO'),
    array('user_no' => 10,	'target_no' => null,	'type' => 'DEATH_NOTE_NOT_DO'),
    //array('user_no' => 11,	'target_no' => 16,	'type' => 'JAMMER_MAD_DO'),
    //array('user_no' => 11,	'target_no' => 12,	'type' => 'VOODOO_MAD_DO'),
    //array('user_no' => 11,	'target_no' =>  4,	'type' => 'VOODOO_FOX_DO'),
    //array('user_no' => 11,	'target_no' =>  8,	'type' => 'DREAM_EAT'),
    //array('user_no' => 12,	'target_no' => 12,	'type' => 'TRAP_MAD_DO'),
    //array('user_no' => 12,	'target_no' => null,	'type' => 'TRAP_MAD_NOT_DO'),
    //array('user_no' => 12,	'target_no' => 18,	'type' => 'POSSESSED_DO'),
    //array('user_no' => 12,	'target_no' => null,	'type' => 'POSSESSED_NOT_DO'),
    //array('user_no' => 12,	'target_no' =>  4,	'type' => 'ANTI_VOODOO_DO'),
    //array('user_no' => 12,	'target_no' => 16,	'type' => 'MAGE_DO'),
    //array('user_no' => 12,	'target_no' =>  2,	'type' => 'WOLF_EAT'),
    //array('user_no' => 12,	'target_no' => 11,	'type' => 'VOODOO_FOX_DO'),
    //array('user_no' => 13,	'target_no' =>  8,	'type' => 'POSSESSED_DO'),
    //array('user_no' => 13,	'target_no' => null,	'type' => 'POSSESSED_NOT_DO'),
    //array('user_no' => 13,	'target_no' =>  6,	'type' => 'POISON_CAT_DO'),
    //array('user_no' => 13,	'target_no' => null,	'type' => 'POISON_CAT_NOT_DO'),
    //array('user_no' => 13,	'target_no' => 13,	'type' => 'TRAP_MAD_DO'),
    //array('user_no' => 13,	'target_no' => null,	'type' => 'TRAP_MAD_NOT_DO'),
    //array('user_no' => 13,	'target_no' =>  3,	'type' => 'VOODOO_KILLER_DO'),
    //array('user_no' => 13,	'target_no' => '12 13 14',	'type' => 'STEP_DO'),
    //array('user_no' => 14,	'target_no' => 18,	'type' => 'CHILD_FOX_DO'),
    //array('user_no' => 14,	'target_no' => 13,	'type' => 'VOODOO_KILLER_DO'),
    //array('user_no' => 14,	'target_no' =>  5,	'type' => 'JAMMER_MAD_DO'),
    //array('user_no' => 16,	'target_no' => '11 6 1 2 3 4 5',	'type' => 'STEP_MAGE_DO'),
    //array('user_no' => 17,	'target_no' => 22,	'type' => 'FAIRY_DO'),
    //array('user_no' => 18,	'target_no' => 20,	'type' => 'VOODOO_FOX_DO'),
    //array('user_no' => 18,	'target_no' => '13 8 9 10',	'type' => 'STEP_WOLF_EAT'),
    //array('user_no' => 18,	'target_no' => '13 8 9 10',	'type' => 'SILENT_WOLF_EAT'),
    //array('user_no' => 18,	'target_no' => '17 16 11',	'type' => 'STEP_VAMPIRE_DO'),
    //array('user_no' => 19,	'target_no' => 14,	'type' => 'FAIRY_DO'),
    array('user_no' => 19,	'target_no' => '3 7 22',	'type' => 'SPREAD_WIZARD_DO'),
    array('user_no' => 20,	'target_no' =>  8,	'type' => 'VAMPIRE_DO'),
    //array('user_no' => 21,	'target_no' =>  5,	'type' => 'CHILD_FOX_DO'),
    //array('user_no' => 22,	'target_no' => 23,	'type' => 'ESCAPE_DO'),
    array('user_no' => 22,	'target_no' => 28,	'type' => 'FAIRY_DO'),
    //array('user_no' => 22,	'target_no' => 22,	'type' => 'TRAP_MAD_DO'),
    //array('user_no' => 22,	'target_no' => 24,	'type' => 'OGRE_DO'),
    //array('user_no' => 22,	'target_no' => null,	'type' => 'OGRE_NOT_DO'),
    //array('user_no' => 22,	'target_no' => 23,	'type' => 'WIZARD_DO'),
    //array('user_no' => 23,	'target_no' => 21,	'type' => 'REPORTER_DO'),
    //array('user_no' => 23,	'target_no' => 11,	'type' => 'ESCAPE_DO'),
    //array('user_no' => 23,	'target_no' => 11,	'type' => 'REPORTER_DO'),
    //array('user_no' => 23,	'target_no' =>  8,	'type' => 'ASSASSIN_DO'),
    //array('user_no' => 23,	'target_no' => 10,	'type' => 'MIND_SCANNER_DO'),
    array('user_no' => 23,	'target_no' =>  2,	'type' => 'TENGU_DO'),
    //array('user_no' => 23,	'target_no' => '12 13 14',	'type' => 'STEP_SCANNER_DO'),
    //array('user_no' => 23,	'target_no' => 16,	'type' => 'VAMPIRE_DO'),
    //array('user_no' => 24,	'target_no' => 22,	'type' => 'MIND_SCANNER_DO'),
    //array('user_no' => 24,	'target_no' => 11,	'type' => 'WIZARD_DO'),
    //array('user_no' => 24,	'target_no' => '3 9',	'type' => 'SPREAD_WIZARD_DO'),
    //array('user_no' => 24,	'target_no' => 12,	'type' => 'SPREAD_WIZARD_DO'),
    //array('user_no' => 24,	'target_no' => 22,	'type' => 'GUARD_DO'),
    //array('user_no' => 24,	'target_no' => 28,	'type' => 'ESCAPE_DO'),
    //array('user_no' => 25,	'target_no' => 22,	'type' => 'TRAP_MAD_DO'),
    array('user_no' => 25,	'target_no' => 30,	'type' => 'OGRE_DO'),
    //array('user_no' => 25,	'target_no' => null,	'type' => 'OGRE_NOT_DO'),
    array('user_no' => 29,	'target_no' =>  8,	'type' => 'TENGU_DO'),
    //array('user_no' => 30,	'target_no' => '12 13 14',	'type' => 'STEP_SCANNER_DO'),
    //array('user_no' => 30,	'target_no' => null,	'type' => 'TRAP_MAD_NOT_DO'),
    //array('user_no' => 30,	'target_no' =>  5,	'type' => 'JAMMER_MAD_DO'),
    //array('user_no' => 30,	'target_no' => 13,	'type' => 'VOODOO_KILLER_DO'),
  );
}

//-- 仮想システムメッセージをセット --//
RQ::GetTest()->winner = 'wolf';
RQ::GetTest()->system_message = array(
  //-- 仮想イベントをセット --//
  3 => array(
	     #'WEATHER'   => array(54),
	     #'VOTE_DUEL' => array(8)
  ),
  4 => array(
	     'EVENT'   => array('blinder'),
	     #'WEATHER' => array(49),
  ),
  5 => array('WEATHER' => array(69)),
  //7 => array('WEATHER' => array(19)),
);

//-- 仮想発言をセット --//
RQ::Set('say', '');
//RQ::Get()->say = "占いCO！\n赤は村人！今日は木曜日ですよwww？";
RQ::Get()->font_type = TalkVoice::WEAK; TalkVoice::NORMAL;

//-- データ収集 --//
//DB::Connect(); //DB接続 (必要なときだけ設定する)
DevRoom::Load();

DB::$ROOM->date = $set_date;
#DB::$ROOM->SetScene(RoomScene::BEFORE);
#DB::$ROOM->SetScene(RoomScene::DAY);
DB::$ROOM->SetScene(RoomScene::NIGHT);
#DB::$ROOM->SetScene(RoomScene::AFTER);
//DB::$ROOM->system_time = Time::Get(); //現在時刻を取得

DevUser::Load();
DB::$USER->SetEvent(); //天候テスト用
#DB::$USER->ByID(9)->live = UserLive::LIVE;
DB::LoadDummyBoy();
#DB::LoadSelf(24);
//DB::$SELF = DB::$USER->TraceExchange(14);

foreach (DB::$USER->rows as $user) {
  if (! isset($user->target_no)) $user->target_no = 0;
  if ($user->IsLive()) {
    RQ::GetTest()->talk_count[$user->id] = $user->id;
  }
}

//沈黙禁止
RQ::GetTest()->talk_count[24] = 0;
unset(RQ::GetTest()->talk_count[3]);
unset(RQ::GetTest()->talk_count[22]);

if ($talk_view_mode) { //発言表示モード
  RQ::GetTest()->talk_data = new StdClass();
  //昼の発言
  $stack = array(
    array('uname' => 'moon',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => '●かー'),
    array('uname' => 'light_blue',
	  'font_type' => TalkVoice::WEAK, 'sentence' => 'えっ'),
    array('uname' => 'green',
	  'location' => TalkLocation::SYSTEM, 'action' => 'OBJECTION'),
    array('uname' => 'dark_gray',
	  'font_type' => TalkVoice::WEAK, 'sentence' => 'チラッ'),
    array('uname' => 'yellow',
	  'font_type' => TalkVoice::STRONG, 'sentence' => "占いCO\n黒は●"),
    array('uname' => 'light_gray',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => 'おはよう'),
    array('uname' => GM::SYSTEM,
	  'location' => TalkLocation::SYSTEM, 'action' => TalkAction::MORNING,
	  'sentence' => DB::$ROOM->date),
  );
  foreach ($stack as &$list) {
    $list['scene'] = RoomScene::DAY;
  }
  RQ::GetTest()->talk_data->day = $stack;

  $stack = array(
    array('uname' => 'cloud',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => '吸血鬼なんだ'),
    array('uname' => 'light_blue',
	  'font_type' => TalkVoice::WEAK, 'sentence' => 'えっ'),
    array('uname' => 'moon',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => 'あーうー'),
    array('uname' => 'gold',
	  'location' => TalkLocation::COMMON, 'font_type' => TalkVoice::NORMAL,
	  'sentence' => 'やあやあ'),
    array('uname' => 'rose',
	  'font_type' => TalkVoice::STRONG, 'sentence' => '誰吸血しようかな'),
    array('uname' => 'frame',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => 'どうしよう'),
    array('uname' => 'black',
	  'location' => TalkLocation::FOX, 'font_type' => TalkVoice::WEAK,
	  'sentence' => '占い師早く死んで欲しいなぁ'),
    array('uname' => 'cherry',
	  'location' => TalkLocation::MAD, 'font_type' => TalkVoice::WEAK,
	  'sentence' => 'やあ'),
    array('uname' => 'green',
	  'font_type' => TalkVoice::NORMAL, 'sentence' => 'てすてす'),
    array('uname' => 'dark_gray',
	  'font_type' => TalkVoice::WEAK, 'sentence' => 'チラッ'),
    array('uname' => 'yellow',
	  'font_type' => TalkVoice::STRONG, 'sentence' => "占いCO\n黒は●"),
    array('uname' => 'light_gray',
	  'location' => TalkLocation::WOLF, 'font_type' => TalkVoice::NORMAL,
	  'sentence' => '生き延びたか'),
    array('uname' => TalkLocation::SYSTEM, 'action' => TalkAction::NIGHT)
  );
  foreach ($stack as &$list) {
    $list['scene'] = RoomScene::NIGHT;
    if (! isset($list['location'])) {
      if ($list['uname'] == GM::SYSTEM) {
        $list['location'] = TalkLocation::SYSTEM;
      } else {
        $list['location'] = TalkLocation::MONOLOGUE;
      }
    }
  }
  RQ::GetTest()->talk_data->night = $stack;
}

//-- データ出力 --//
if ($vote_view_mode) VoteTest::OutputVote(); //投票表示モード
if ($cast_view_mode) VoteTest::OutputCast(); //配役情報表示モード
if ($talk_view_mode) VoteTest::OutputTalk(); //発言表示モード
if ($role_view_mode) VoteTest::OutputImage($role_view_list); //画像表示モード

HTML::OutputHeader('投票テスト', 'game_play', true);
GameHTML::OutputPlayer();
GameHTML::OutputDead();
RoleHTML::OutputAbility();
VoteTest::ConvertTalk(); //発言変換テスト

if (DB::$ROOM->IsDay()) { //昼の投票テスト
  VoteTest::AggregateDay();
}
elseif (DB::$ROOM->IsNight()) { // 夜の投票テスト
  //Text::p(RQ::GetTest()->vote->night);
  VoteNight::Aggregate();
}
elseif (DB::$ROOM->IsAfterGame()) { //勝敗判定表示
  VoteTest::OutputWinner();
}

//Text::p(RQ::GetTest()->system_message, '◆System');
//Text::p(RQ::GetTest()->result_ability, '◆Ability');
//Text::p(RQ::GetTest()->result_dead,    '◆Dead');
VoteTest::OutputResult();

/* デバッグ情報 */
//Text::p(Loader::$file, '◆Loader');
//foreach (DB::$ROOM as $name => $class) Text::p($class, $name);
//foreach (DB::$ROOM->Stack() as $key => $data) Text::p($data, "◆Stack [{$key}]");
//Text::p(DB::$ROOM->Flag(), '◆Flag');
//DB::$ROOM->Stack()->p('event', '◆Event');
//Text::p(DB::$ROOM->game_option);
//Text::p(DB::$USER->role);
//Text::v(DB::$USER->IsOpenCast(), '◆OpenCast');
//Text::p(RoleManager::$file, '◆Role [file]');
//Text::p(array_keys(RoleManager::$class), '◆Role [class]');
//foreach (RoleManager::$class as $name => $class) Text::p($class, $name);
//InsertLog();

/* 役職判定情報 */
//VoteTest::OutputDistinguishMage();

/* 配役情報 */
//Loader::LoadFile('chaos_config');
//Text::p(Lottery::ToProbability(ChaosConfig::$chaos_hyper_random_role_list))	//確率
//Text::p(array_sum(ChaosConfig::$chaos_hyper_random_role_list))		//確率合計
//VoteTest::OutputChaosSumGroup();						//系列合計
//Text::p(ChaosConfig::$role_group_rate_list);					//系列係数

/* 天候情報 */
//Text::p(Lottery::ToProbability(GameConfig::$weather_list));

/* DB テスト */
//DB::Connect(); DB::d();
//Text::p($stack);

HTML::OutputFooter(true);
