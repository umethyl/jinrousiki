<?php
error_reporting(E_ALL);
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
$INIT_CONF->LoadClass('ROOM_CONF', 'ICON_CONF', 'ROLES');
$INIT_CONF->LoadFile('game_vote_functions', 'game_play_functions');

//-- 仮想村データをセット --//
$INIT_CONF->LoadRequest('RequestBaseGame');
$RQ_ARGS->reverse_log = null;
$RQ_ARGS->room_no = 94;
$RQ_ARGS->TestItems = new StdClass();
$RQ_ARGS->TestItems->test_room = array(
  'id' => $RQ_ARGS->room_no,
  'name' => '投票テスト村',
  'comment' => '',
  //'game_option' => 'dummy_boy full_mania chaosfull chaos_open_cast no_sub_role real_time:6:4 joker',
  'game_option' => 'dummy_boy chaosfull chaos_open_cast no_sub_role real_time:6:4 joker weather',
  'date' => 9,
  'day_night' => 'night',
  //'day_night' => 'aftergame',
  'status' => 'playing'
  //'status' => 'finished'
);
$RQ_ARGS->TestItems->test_room['game_option'] .= ' not_open_cast';
$RQ_ARGS->TestItems->test_room['game_option'] .= ' open_vote death_note';
#$RQ_ARGS->TestItems->test_room['game_option'] .= ' seal_message';
#$RQ_ARGS->TestItems->test_room['game_option'] .= ' quiz';
$RQ_ARGS->TestItems->is_virtual_room = true;
$RQ_ARGS->vote_times = 1;
$RQ_ARGS->TestItems->test_users = array();
for($id = 1; $id <= 25; $id++) $RQ_ARGS->TestItems->test_users[$id] = new User();

$RQ_ARGS->TestItems->test_users[1]->uname = 'dummy_boy';
$RQ_ARGS->TestItems->test_users[1]->handle_name = '身代わり君';
$RQ_ARGS->TestItems->test_users[1]->sex = 'female';
$RQ_ARGS->TestItems->test_users[1]->role = 'resurrect_mania';
$RQ_ARGS->TestItems->test_users[1]->live = 'dead';
$RQ_ARGS->TestItems->test_users[1]->icon_filename = '../img/dummy_boy_user_icon.jpg';
$RQ_ARGS->TestItems->test_users[1]->color = '#000000';

$RQ_ARGS->TestItems->test_users[2]->uname = 'light_gray';
$RQ_ARGS->TestItems->test_users[2]->handle_name = '明灰';
$RQ_ARGS->TestItems->test_users[2]->sex = 'male';
$RQ_ARGS->TestItems->test_users[2]->role = 'sex_wolf';
$RQ_ARGS->TestItems->test_users[2]->live = 'live';

$RQ_ARGS->TestItems->test_users[3]->uname = 'dark_gray';
$RQ_ARGS->TestItems->test_users[3]->handle_name = '暗灰';
$RQ_ARGS->TestItems->test_users[3]->sex = 'male';
$RQ_ARGS->TestItems->test_users[3]->role = 'possessed_wolf possessed_target[3-17]';
$RQ_ARGS->TestItems->test_users[3]->live = 'live';

$RQ_ARGS->TestItems->test_users[4]->uname = 'yellow';
$RQ_ARGS->TestItems->test_users[4]->handle_name = '黄色';
$RQ_ARGS->TestItems->test_users[4]->sex = 'female';
$RQ_ARGS->TestItems->test_users[4]->role = 'psycho_mage lovers[16] challenge_lovers';
$RQ_ARGS->TestItems->test_users[4]->live = 'live';

$RQ_ARGS->TestItems->test_users[5]->uname = 'orange';
$RQ_ARGS->TestItems->test_users[5]->handle_name = 'オレンジ';
$RQ_ARGS->TestItems->test_users[5]->sex = 'female';
$RQ_ARGS->TestItems->test_users[5]->role = 'soul_mage febris[6]';
$RQ_ARGS->TestItems->test_users[5]->live = 'live';

$RQ_ARGS->TestItems->test_users[6]->uname = 'red';
$RQ_ARGS->TestItems->test_users[6]->handle_name = '赤';
$RQ_ARGS->TestItems->test_users[6]->sex = 'female';
$RQ_ARGS->TestItems->test_users[6]->role = 'seal_medium mind_friend[21] possessed[4-15]';
$RQ_ARGS->TestItems->test_users[6]->live = 'dead';

$RQ_ARGS->TestItems->test_users[7]->uname = 'light_blue';
$RQ_ARGS->TestItems->test_users[7]->handle_name = '水色';
$RQ_ARGS->TestItems->test_users[7]->sex = 'male';
$RQ_ARGS->TestItems->test_users[7]->role = 'reflect_guard';
$RQ_ARGS->TestItems->test_users[7]->live = 'live';

$RQ_ARGS->TestItems->test_users[8]->uname = 'blue';
$RQ_ARGS->TestItems->test_users[8]->handle_name = '青';
$RQ_ARGS->TestItems->test_users[8]->sex = 'male';
$RQ_ARGS->TestItems->test_users[8]->role = 'anti_voodoo';
$RQ_ARGS->TestItems->test_users[8]->live = 'live';

$RQ_ARGS->TestItems->test_users[9]->uname = 'green';
$RQ_ARGS->TestItems->test_users[9]->handle_name = '緑';
$RQ_ARGS->TestItems->test_users[9]->sex = 'female';
$RQ_ARGS->TestItems->test_users[9]->role = 'missfire_cat joker[3]';
$RQ_ARGS->TestItems->test_users[9]->live = 'live';

$RQ_ARGS->TestItems->test_users[10]->uname = 'purple';
$RQ_ARGS->TestItems->test_users[10]->handle_name = '紫';
$RQ_ARGS->TestItems->test_users[10]->sex = 'female';
$RQ_ARGS->TestItems->test_users[10]->role = 'soul_assassin death_note[5]';
$RQ_ARGS->TestItems->test_users[10]->live = 'live';

$RQ_ARGS->TestItems->test_users[11]->uname = 'cherry';
$RQ_ARGS->TestItems->test_users[11]->handle_name = 'さくら';
$RQ_ARGS->TestItems->test_users[11]->sex = 'female';
$RQ_ARGS->TestItems->test_users[11]->role = 'voodoo_mad';
$RQ_ARGS->TestItems->test_users[11]->live = 'live';

$RQ_ARGS->TestItems->test_users[12]->uname = 'white';
$RQ_ARGS->TestItems->test_users[12]->handle_name = '白';
$RQ_ARGS->TestItems->test_users[12]->sex = 'male';
$RQ_ARGS->TestItems->test_users[12]->role = 'trap_mad';
$RQ_ARGS->TestItems->test_users[12]->live = 'live';

$RQ_ARGS->TestItems->test_users[13]->uname = 'black';
$RQ_ARGS->TestItems->test_users[13]->handle_name = '黒';
$RQ_ARGS->TestItems->test_users[13]->sex = 'male';
$RQ_ARGS->TestItems->test_users[13]->role = 'swindle_mad';
$RQ_ARGS->TestItems->test_users[13]->live = 'live';

$RQ_ARGS->TestItems->test_users[14]->uname = 'gold';
$RQ_ARGS->TestItems->test_users[14]->handle_name = '金';
$RQ_ARGS->TestItems->test_users[14]->sex = 'female';
$RQ_ARGS->TestItems->test_users[14]->role = 'divorce_jealousy panelist disfavor';
$RQ_ARGS->TestItems->test_users[14]->live = 'live';

$RQ_ARGS->TestItems->test_users[15]->uname = 'frame';
$RQ_ARGS->TestItems->test_users[15]->handle_name = '炎';
$RQ_ARGS->TestItems->test_users[15]->sex = 'female';
$RQ_ARGS->TestItems->test_users[15]->role = 'possessed_fox possessed_target[4-6] lost_ability';
$RQ_ARGS->TestItems->test_users[15]->live = 'live';

$RQ_ARGS->TestItems->test_users[16]->uname = 'scarlet';
$RQ_ARGS->TestItems->test_users[16]->handle_name = '紅';
$RQ_ARGS->TestItems->test_users[16]->sex = 'female';
$RQ_ARGS->TestItems->test_users[16]->role = 'moon_cupid lovers[16] challenge_lovers';
$RQ_ARGS->TestItems->test_users[16]->live = 'live';

$RQ_ARGS->TestItems->test_users[17]->uname = 'sky';
$RQ_ARGS->TestItems->test_users[17]->handle_name = '空';
$RQ_ARGS->TestItems->test_users[17]->sex = 'male';
$RQ_ARGS->TestItems->test_users[17]->role = 'necromancer possessed[3-3] disfavor';
$RQ_ARGS->TestItems->test_users[17]->live = 'dead';

$RQ_ARGS->TestItems->test_users[18]->uname = 'sea';
$RQ_ARGS->TestItems->test_users[18]->handle_name = '海';
$RQ_ARGS->TestItems->test_users[18]->sex = 'male';
$RQ_ARGS->TestItems->test_users[18]->role = 'revive_priest joker[4]';
$RQ_ARGS->TestItems->test_users[18]->live = 'dead';

$RQ_ARGS->TestItems->test_users[19]->uname = 'land';
$RQ_ARGS->TestItems->test_users[19]->handle_name = '陸';
$RQ_ARGS->TestItems->test_users[19]->sex = 'female';
$RQ_ARGS->TestItems->test_users[19]->role = 'light_fairy psycho_infected';
$RQ_ARGS->TestItems->test_users[19]->live = 'live';

$RQ_ARGS->TestItems->test_users[20]->uname = 'rose';
$RQ_ARGS->TestItems->test_users[20]->handle_name = '薔薇';
$RQ_ARGS->TestItems->test_users[20]->sex = 'female';
$RQ_ARGS->TestItems->test_users[20]->role = 'scarlet_vampire';
$RQ_ARGS->TestItems->test_users[20]->live = 'live';

$RQ_ARGS->TestItems->test_users[21]->uname = 'peach';
$RQ_ARGS->TestItems->test_users[21]->handle_name = '桃';
$RQ_ARGS->TestItems->test_users[21]->sex = 'female';
$RQ_ARGS->TestItems->test_users[21]->role = 'soul_mania[2] mind_friend[21] panelist';
$RQ_ARGS->TestItems->test_users[21]->live = 'live';

$RQ_ARGS->TestItems->test_users[22]->uname = 'gust';
$RQ_ARGS->TestItems->test_users[22]->handle_name = '霧';
$RQ_ARGS->TestItems->test_users[22]->sex = 'female';
$RQ_ARGS->TestItems->test_users[22]->role = 'pierrot_wizard reduce_voter';
$RQ_ARGS->TestItems->test_users[22]->live = 'live';

$RQ_ARGS->TestItems->test_users[23]->uname = 'cloud';
$RQ_ARGS->TestItems->test_users[23]->handle_name = '雲';
$RQ_ARGS->TestItems->test_users[23]->sex = 'male';
$RQ_ARGS->TestItems->test_users[23]->role = 'reporter';
$RQ_ARGS->TestItems->test_users[23]->live = 'live';

$RQ_ARGS->TestItems->test_users[24]->uname = 'moon';
$RQ_ARGS->TestItems->test_users[24]->handle_name = '月';
$RQ_ARGS->TestItems->test_users[24]->sex = 'female';
$RQ_ARGS->TestItems->test_users[24]->role = 'barrier_wizard infected[20]';
$RQ_ARGS->TestItems->test_users[24]->live = 'live';

$RQ_ARGS->TestItems->test_users[25]->uname = 'sun';
$RQ_ARGS->TestItems->test_users[25]->handle_name = '太陽';
$RQ_ARGS->TestItems->test_users[25]->sex = 'male';
$RQ_ARGS->TestItems->test_users[25]->role = 'ogre disfavor';
$RQ_ARGS->TestItems->test_users[25]->live = 'live';
$RQ_ARGS->TestItems->test_users[25]->profile = "あーうー\nうーあー";

//$RQ_ARGS->TestItems->test_users = 30;
$icon_color_list = array('#DDDDDD', '#999999', '#FFD700', '#FF9900', '#FF0000',
			 '#99CCFF', '#0066FF', '#00EE00', '#CC00CC', '#FF9999');
foreach($RQ_ARGS->TestItems->test_users as $id => $user){
  $user->room_no = $RQ_ARGS->room_no;
  $user->user_no = $id;
  if(! property_exists($user, 'profile')) $user->profile = $id;
  $user->last_load_day_night = 'night';
  $user->is_system = $user->user_no == 1;
  if($id > 1){
    $user->color = $icon_color_list[($id - 2) % 10];
    $user->icon_filename = sprintf('%03d.gif', ($id - 2) % 10 + 1);
  }
}

//-- 仮想投票データをセット --//
$RQ_ARGS->TestItems->vote = new StdClass();
$RQ_ARGS->TestItems->vote->day = array();
$RQ_ARGS->TestItems->vote_target_day_old = array(
  array('id' =>  2, 'target_no' => 11),
  array('id' =>  3, 'target_no' =>  7),
  //array('id' =>  3, 'target_no' => 10),
  array('id' =>  4, 'target_no' => 11),
  array('id' =>  5, 'target_no' => 25),
  //array('id' =>  6, 'target_no' =>  3),
  //array('id' =>  7, 'target_no' =>  3),
  array('id' =>  7, 'target_no' => 25),
  array('id' =>  8, 'target_no' =>  9),
  array('id' =>  9, 'target_no' =>  3),
  array('id' => 10, 'target_no' =>  3),
  array('id' => 11, 'target_no' =>  3),
  array('id' => 12, 'target_no' => 11),
  array('id' => 13, 'target_no' => 11),
  array('id' => 14, 'target_no' => 12),
  array('id' => 15, 'target_no' =>  7),
  array('id' => 16, 'target_no' => 23),
  //array('id' => 17, 'target_no' => 22),
  array('id' => 18, 'target_no' => 25),
  //array('id' => 18, 'target_no' => 3),
  array('id' => 19, 'target_no' => 22),
  array('id' => 20, 'target_no' => 22),
  array('id' => 21, 'target_no' => 18),
  array('id' => 22, 'target_no' => 25),
  array('id' => 23, 'target_no' => 14),
  array('id' => 24, 'target_no' => 25),
  #array('id' => 25, 'target_no' =>  3),
  array('id' => 25, 'target_no' => 12),
);
$RQ_ARGS->TestItems->vote_target_day = array(
  array('id' =>  2, 'target_no' => 11),
  //array('id' =>  3, 'target_no' => 10),
  array('id' =>  4, 'target_no' => 11),
  array('id' =>  5, 'target_no' => 25),
  //array('id' =>  6, 'target_no' =>  3),
  //array('id' =>  7, 'target_no' =>  3),
  array('id' =>  7, 'target_no' => 25),
  array('id' =>  9, 'target_no' =>  3),
  array('id' => 22, 'target_no' => 25),
  array('id' => 10, 'target_no' =>  3),
  array('id' => 11, 'target_no' =>  3),
  array('id' => 12, 'target_no' => 11),
  array('id' => 13, 'target_no' => 11),
  array('id' =>  8, 'target_no' =>  9),
  array('id' => 14, 'target_no' => 12),
  array('id' => 15, 'target_no' =>  7),
  array('id' => 16, 'target_no' => 23),
  //array('id' => 17, 'target_no' => 22),
  array('id' => 18, 'target_no' => 25),
  //array('id' => 18, 'target_no' => 3),
  array('id' =>  3, 'target_no' =>  7),
  array('id' => 20, 'target_no' => 22),
  array('id' => 21, 'target_no' => 18),
  array('id' => 23, 'target_no' => 14),
  array('id' => 19, 'target_no' => 22),
  array('id' => 24, 'target_no' => 25),
  #array('id' => 25, 'target_no' =>  3),
  array('id' => 25, 'target_no' => 12),
);

//初日用
/*
$RQ_ARGS->TestItems->vote->night = array(
  array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'dummy_boy'),
  array('uname' => 'yellow', 'situation' => 'MAGE_DO', 'target_uname' => 'gold'),
  array('uname' => 'orange', 'situation' => 'MAGE_DO', 'target_uname' => 'gold'),
  array('uname' => 'cherry', 'situation' => 'VOODOO_MAD_DO', 'target_uname' => 'yellow'),
  #array('uname' => 'black', 'situation' => 'MAGE_DO', 'target_uname' => 'sea'),
  array('uname' => 'scarlet', 'situation' => 'CUPID_DO', 'target_uname' => 'scarlet sea'),
  array('uname' => 'land', 'situation' => 'FAIRY_DO', 'target_uname' => 'rose'),
  #array('uname' => 'peach', 'situation' => 'CUPID_DO', 'target_uname' => 'sea peach'),
  array('uname' => 'peach', 'situation' => 'MANIA_DO', 'target_uname' => 'sea'),
  #array('uname' => 'gust', 'situation' => 'DUELIST_DO', 'target_uname' => 'moon'),
  #array('uname' => 'cloud', 'situation' => 'MANIA_DO', 'target_uname' => 'yellow'),
  #array('uname' => 'cloud', 'situation' => 'CHILD_FOX_DO', 'target_uname' => 'yellow'),
  #array('uname' => 'moon', 'situation' => 'MIND_SCANNER_DO', 'target_uname' => 'light_gray'),
);
*/

$RQ_ARGS->TestItems->vote->night = array(
  array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'peach'),
  #array('uname' => 'dark_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'green'),
  array('uname' => 'yellow', 'situation' => 'MAGE_DO', 'target_uname' => 'black'),
  array('uname' => 'orange', 'situation' => 'MAGE_DO', 'target_uname' => 'black'),
  array('uname' => 'light_blue', 'situation' => 'GUARD_DO', 'target_uname' => 'sea'),
  #array('uname' => 'blue', 'situation' => 'GUARD_DO', 'target_uname' => 'peach'),
  array('uname' => 'blue', 'situation' => 'ANTI_VOODOO_DO', 'target_uname' => 'dark_gray'),
  array('uname' => 'green', 'situation' => 'POISON_CAT_DO', 'target_uname' => 'frame'),
  #array('uname' => 'green', 'situation' => 'POISON_CAT_NOT_DO', 'target_uname' => null),
  array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'dark_gray'),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_NOT_DO', 'target_uname' => null),
  #array('uname' => 'purple', 'situation' => 'DEATH_NOTE_DO', 'target_uname' => 'white'),
  #array('uname' => 'cherry', 'situation' => 'JAMMER_MAD_DO', 'target_uname' => 'sea'),
  #array('uname' => 'cherry', 'situation' => 'VOODOO_FOX_DO', 'target_uname' => 'yellow'),
  array('uname' => 'cherry', 'situation' => 'VOODOO_MAD_DO', 'target_uname' => 'yellow'),
  #array('uname' => 'cherry', 'situation' => 'DREAM_EAT', 'target_uname' => 'light_blue'),
  array('uname' => 'white', 'situation' => 'TRAP_MAD_DO',	'target_uname' => 'sea'),
  #array('uname' => 'white', 'situation' => 'TRAP_MAD_NOT_DO',	'target_uname' => null),
  #array('uname' => 'white', 'situation' => 'POSSESSED_DO',	'target_uname' => 'cloud'),
  #array('uname' => 'white', 'situation' => 'POSSESSED_NOT_DO',	'target_uname' => null),
  #array('uname' => 'white', 'situation' => 'ANTI_VOODOO_DO',	'target_uname' => 'yellow'),
  #array('uname' => 'black', 'situation' => 'MAGE_DO', 'target_uname' => 'sea'),
  #array('uname' => 'black', 'situation' => 'WOLF_EAT', 'target_uname' => 'light_gray'),
  #array('uname' => 'black', 'situation' => 'VOODOO_FOX_DO',	'target_uname' => 'peach'),
  #array('uname' => 'gold', 'situation' => 'POSSESSED_DO',	'target_uname' => 'cloud'),
  #array('uname' => 'gold', 'situation' => 'POSSESSED_NOT_DO',	'target_uname' => null),
  #array('uname' => 'gold', 'situation' => 'POISON_CAT_DO',	'target_uname' => 'red'),
  #array('uname' => 'gold', 'situation' => 'POISON_CAT_NOT_DO',	'target_uname' => null),
  #array('uname' => 'gold', 'situation' => 'TRAP_MAD_DO',	'target_uname' => 'gold'),
  #array('uname' => 'gold', 'situation' => 'TRAP_MAD_NOT_DO',	'target_uname' => null),
  #array('uname' => 'gold', 'situation' => 'VOODOO_KILLER_DO',	'target_uname' => 'light_gray'),
  #array('uname' => 'frame', 'situation' => 'CHILD_FOX_DO',	'target_uname' => 'peach'),
  #array('uname' => 'frame', 'situation' => 'VOODOO_KILLER_DO',	'target_uname' => 'purple'),
  #array('uname' => 'frame', 'situation' => 'JAMMER_MAD_DO',	'target_uname' => 'orange'),
  #array('uname' => 'sky', 'situation' => 'CUPID_DO', 'target_uname' => 'orange purple'),
  #array('uname' => 'sea', 'situation' => 'FAIRY_DO', 'target_uname' => 'gust'),
  #array('uname' => 'land', 'situation' => 'VOODOO_FOX_DO', 'target_uname' => 'rose'),
  array('uname' => 'land', 'situation' => 'FAIRY_DO', 'target_uname' => 'sea'),
  array('uname' => 'rose', 'situation' => 'VAMPIRE_DO', 'target_uname' => 'cloud'),
  #array('uname' => 'peach', 'situation' => 'CHILD_FOX_DO',	'target_uname' => 'orange'),
  #array('uname' => 'gust', 'situation' => 'ESCAPE_DO', 'target_uname' => 'black'),
  #array('uname' => 'gust', 'situation' => 'FAIRY_DO', 'target_uname' => 'cloud'),
  #array('uname' => 'gust', 'situation' => 'TRAP_MAD_DO', 'target_uname' => 'gust'),
  #array('uname' => 'gust', 'situation' => 'OGRE_DO', 'target_uname' => 'moon'),
  #array('uname' => 'gust', 'situation' => 'OGRE_NOT_DO', 'target_uname' => null),
  array('uname' => 'gust', 'situation' => 'WIZARD_DO', 'target_uname' => 'land'),
  array('uname' => 'cloud', 'situation' => 'REPORTER_DO', 'target_uname' => 'sea'),
  #array('uname' => 'cloud', 'situation' => 'ESCAPE_DO', 'target_uname' => 'cherry'),
  #array('uname' => 'cloud', 'situation' => 'REPORTER_DO', 'target_uname' => 'gold'),
  #array('uname' => 'cloud', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'dark_gray'),
  #array('uname' => 'cloud', 'situation' => 'MIND_SCANNER_DO', 'target_uname' => 'purple'),
  #array('uname' => 'cloud', 'situation' => 'VAMPIRE_DO', 'target_uname' => 'sea'),
  #array('uname' => 'moon', 'situation' => 'MIND_SCANNER_DO', 'target_uname' => 'light_gray'),
  #array('uname' => 'moon', 'situation' => 'WIZARD_DO', 'target_uname' => 'white'),
  array('uname' => 'moon', 'situation' => 'SPREAD_WIZARD_DO', 'target_uname' => '12 13 18'),
  #array('uname' => 'moon', 'situation' => 'SPREAD_WIZARD_DO', 'target_uname' => '12'),
  #array('uname' => 'sun', 'situation' => 'TRAP_MAD_DO', 'target_uname' => 'gust'),
  array('uname' => 'sun', 'situation' => 'OGRE_DO', 'target_uname' => 'white'),
  #array('uname' => 'sun', 'situation' => 'OGRE_NOT_DO', 'target_uname' => null),
);

//-- 仮想システムメッセージをセット --//
$RQ_ARGS->TestItems->system_message = array();
$RQ_ARGS->TestItems->victory = 'wolf';

//-- 仮想イベントをセット --//
$RQ_ARGS->TestItems->event = array(
  #array('type' => 'VOTE_KILLED', 'message' => 'light_gray'),
  #array('type' => 'VOTE_KILLED', 'message' => 'sky'),
  #array('type' => 'WOLF_KILLED', 'message' => 'dummy_boy'),
  array('type' => 'WEATHER', 'message' => 0)
  #array('type' => 'WEATHER', 'message' => $GAME_CONF->GetWeather())
);

//-- 仮想発現をセット --//
#$RQ_ARGS->say = "占いCO！\n赤は村人！今日は木曜日ですよwww？";
$RQ_ARGS->say = '';
$RQ_ARGS->font_type = 'weak'; 'normal';

//-- データ収集 --//
#$DB_CONF->Connect(); // DB 接続
$ROOM = new Room($RQ_ARGS); //村情報を取得
$ROOM->test_mode = true;
$ROOM->log_mode = true;
$ROOM->date = 3;
#$ROOM->day_night = 'beforegame';
#$ROOM->day_night = 'day';
$ROOM->day_night = 'night';
#$ROOM->day_night = 'aftergame';
//$ROOM->system_time = TZTime(); //現在時刻を取得
$USERS = new UserDataSet($RQ_ARGS); //ユーザ情報をロード
#foreach($USERS->rows as $user) $user->live = 'live'; //初日用
$USERS->ByID(9)->live = 'live';
#$SELF = new User();
$SELF = $USERS->ByID(1);
#$SELF = $USERS->ByID(13);
#$SELF = $USERS->TraceExchange(14);

//-- データ出力 --//
$vote_view_mode = false;
if($vote_view_mode){ //投票表示モード
  $INIT_CONF->LoadClass('VOTE_MESS');
  $stack = new RequestGameVote();
  $RQ_ARGS->vote = $stack->vote;
  $RQ_ARGS->target_no = $stack->target_no;
  $RQ_ARGS->situation = $stack->situation;
  $RQ_ARGS->back_url  = '';
  if($RQ_ARGS->vote){ //投票処理
    OutputHTMLHeader('投票テスト', 'game'); //HTMLヘッダ
    echo '</head><body>'."\n";
    if($RQ_ARGS->target_no == 0){ //空投票検出
      OutputActionResult('空投票', '投票先を指定してください');
    }
    elseif($ROOM->IsDay()){ //昼の処刑投票処理
      #VoteDay();
    }
    elseif($ROOM->IsNight()){ //夜の投票処理
      VoteNight();
    }
    else{ //ここに来たらロジックエラー
      OutputVoteError('投票コマンドエラー', '投票先を指定してください');
    }
  }
  else{
    $RQ_ARGS->post_url = 'vote_test.php';
    switch($ROOM->day_night){
    case 'beforegame':
      OutputVoteBeforeGame();
      break;

    case 'day':
      OutputVoteDay();
      break;

    case 'night':
      OutputVoteNight();
      break;

    default: //ここに来たらロジックエラー
      OutputVoteError('投票シーンエラー');
      break;
    }
  }
  $SELF = $USERS->ByID(1);
  OutputPlayerList();
  OutputHTMLFooter(true);
}
OutputHTMLHeader('投票テスト', 'game'); //HTMLヘッダ
$talk_view_mode = false;
if($talk_view_mode){ //発言表示モード
  echo $ROOM->GenerateCSS();
  echo '</head><body>'."\n";
  $INIT_CONF->LoadFile('talk_class');
  //$query = 'SELECT uname, sentence, font_type, location FROM talk' . $this->GetQuery(! $heaven) .
  $RQ_ARGS->add_role = false;
  $RQ_ARGS->TestItems->talk_data = new StdClass();
  $RQ_ARGS->TestItems->talk_data->day = array(
    array('uname' => 'moon', 'location' => 'day', 'font_type' => 'normal', 'sentence' => '●かー'),
    array('uname' => 'light_blue', 'location' => 'day', 'font_type' => 'weak', 'sentence' => 'えっ'),
    array('uname' => 'green', 'location' => 'day system', 'font_type' => null,
	  'sentence' => 'OBJECTION	緑'),
    array('uname' => 'dark_gray', 'location' => 'day', 'font_type' => 'weak', 'sentence' => 'ﾁﾗｯ'),
    array('uname' => 'yellow', 'location' => 'day', 'font_type' => 'strong',
	  'sentence' => "占いCO\n黒は●"),
    array('uname' => 'light_gray', 'location' => 'day', 'font_type' => 'normal', 'sentence' => 'おはよう'),
    array('uname' => 'system', 'location' => 'day system', 'font_type' => null,
	  'sentence' => 'MORNING	' . $ROOM->date),
  );
  $RQ_ARGS->TestItems->talk_data->night = array(
    array('uname' => 'cloud', 'location' => 'night self_talk', 'font_type' => 'normal',
	  'sentence' => '吸血鬼なんだ'),
    array('uname' => 'light_blue', 'location' => 'night self_talk', 'font_type' => 'weak',
	  'sentence' => 'えっ'),
    array('uname' => 'moon', 'location' => 'night self_talk', 'font_type' => 'normal',
	  'sentence' => 'あーうー'),
    array('uname' => 'gold', 'location' => 'night common', 'font_type' => 'normal',
	  'sentence' => 'やあやあ'),
    array('uname' => 'rose', 'location' => 'night self_talk', 'font_type' => 'strong',
	  'sentence' => '誰吸血しようかな'),
    array('uname' => 'frame', 'location' => 'night self_talk', 'font_type' => 'normal',
	  'sentence' => 'どうしよう'),
    array('uname' => 'black', 'location' => 'night fox', 'font_type' => 'weak',
	  'sentence' => '占い師早く死んで欲しいなぁ'),
    array('uname' => 'cherry', 'location' => 'night mad', 'font_type' => 'weak', 'sentence' => 'やあ'),
    array('uname' => 'green', 'location' => 'night self_talk', 'font_type' => 'normal',
	  'sentence' => 'てすてす'),
    array('uname' => 'dark_gray', 'location' => 'night self_talk', 'font_type' => 'weak',
	  'sentence' => 'ﾁﾗｯ'),
    array('uname' => 'yellow', 'location' => 'night self_talk', 'font_type' => 'strong',
	  'sentence' => "占いCO\n黒は●"),
    array('uname' => 'light_gray', 'location' => 'night wolf', 'font_type' => 'normal',
	  'sentence' => '生き延びたか'),
    array('uname' => 'system', 'location' => 'night system', 'font_type' => null, 'sentence' => 'NIGHT')
  );
  $RQ_ARGS->TestItems->talk = array();
  foreach($RQ_ARGS->TestItems->talk_data->{$ROOM->day_night} as $stack){
    $RQ_ARGS->TestItems->talk[] = new Talk($stack);
  }
  //PrintData($RQ_ARGS->TestItems->talk);
  OutputPlayerList();
  if($SELF->user_no > 0) OutputAbility();
  OutputTalkLog();
  OutputHTMLFooter(true);
}
echo '</head><body>'."\n";
$role_view_mode = false;
if($role_view_mode){ //画像表示モード
  foreach(array_keys($ROLE_DATA->main_role_list) as $role) $ROLE_IMG->Output($role);
  #foreach(array_keys($ROLE_DATA->sub_role_list)  as $role) $ROLE_IMG->Output($role);
  #foreach(array_keys($ROLE_DATA->main_role_list) as $role) $ROLE_IMG->Output('result_'.$role);
  $header = 'prediction_weather_';
  #foreach($ROLE_DATA->weather_list as $stack) $ROLE_IMG->Output($header.$stack['event']);
  OutputHTMLFooter(true);
}
$cast_view_mode = false;
if($cast_view_mode){ //配役情報表示モード
  $INIT_CONF->LoadClass('CAST_CONF');
  //PrintData($CAST_CONF->RateToProbability($CAST_CONF->chaos_hyper_random_role_list));
  //PrintData(array_sum($CAST_CONF->chaos_hyper_random_role_list));
  //PrintData($CAST_CONF->chaos_role_group_rate_list);
  echo '<table border="1" cellspacing="0">'."\n".'<tr><th>人口</th>';
  foreach($CAST_CONF->chaos_role_group_rate_list as $group => $rate){
    $role  = $ROLE_DATA->DistinguishRoleGroup($group);
    $class = $ROLE_DATA->DistinguishRoleClass($role);
    echo '<th class="' . $class . '">' . $ROLE_DATA->short_role_list[$role] . '</th>';
  }
  echo '</tr>'."\n";
  for($i = 8; $i <= 32; $i++){
    echo '<tr align="right"><td><strong>' . $i . '</strong></td>';
    foreach($CAST_CONF->chaos_role_group_rate_list as $rate){
      echo '<td>' . round($i / $rate) . '</td>';
    }
    echo '</tr>'."\n";
  }
  echo '</table>';
  OutputHTMLFooter(true);
}
OutputPlayerList(); //プレイヤーリスト
OutputAbility();
if($RQ_ARGS->say != ''){ //発言変換テスト
  ConvertSay($RQ_ARGS->say);
  Write($RQ_ARGS->say, 'day', 0);
}
if($ROOM->IsDay()){ //昼の投票テスト
  $self_id = $SELF->user_no;
  $RQ_ARGS->situation = 'VOTE_KILL';
  $RQ_ARGS->back_url = '';
  foreach($RQ_ARGS->TestItems->vote_target_day as $stack){
    $SELF = $USERS->ByID($stack['id']);
    $RQ_ARGS->target_no = $stack['target_no'];
    VoteDay();
  }
  $vote_message_list = AggregateVoteDay();
  if(! is_array($vote_message_list)) $vote_message_list = array();
  $stack = array();
  foreach($vote_message_list as $uname => $vote_data){
    array_unshift($vote_data, $USERS->GetHandleName($uname));
    $vote_data[] = $RQ_ARGS->vote_times;
    $stack[] = implode("\t", $vote_data);
  }
  echo GenerateVoteList($stack, $ROOM->date);
  $ROOM->date++;
  //$ROOM->log_mode = false; //イベント確認用
  //$ROOM->day_night = 'day'; //イベント確認用
  $ROOM->day_night = 'night';
  $SELF = $USERS->ByID($self_id);
}
elseif($ROOM->IsNight()){ // 夜の投票テスト
  //PrintData($RQ_ARGS->TestItems->vote->night);
  AggregateVoteNight();
}
elseif($ROOM->IsAfterGame()){ //勝敗判定表示
  $INIT_CONF->LoadClass('VICT_MESS');
  $ROOM->log_mode = false;
  $ROOM->personal_mode = true; false;
  OutputVictory();
  OutputHTMLFooter(); //HTMLフッタ
}
//PrintData($RQ_ARGS->TestItems->system_message);

do{
  //break;
  foreach($USERS->rows as $user){
    unset($user->virtual_role);
    $user->live = $user->IsLive(true) ? 'live' : 'dead';
    $user->ReparseRoles();
  }

  foreach($RQ_ARGS->TestItems->vote->night as $stack){
    $uname = $USERS->GetHandleName($stack['uname'], true);
    switch($stack['situation']){
    case 'CUPID_DO':
      $target_stack = array();
      foreach(explode(' ', $stack['target_uname']) as $target){
	$target_stack[] = $USERS->GetHandleName($target, true);
      }
      $target_uname = implode(' ', $target_stack);
      break;

    case 'SPREAD_WIZARD_DO':
      $target_stack = array();
      foreach(explode(' ', $stack['target_uname']) as $id){
	$user = $USERS->ByVirtual($id);
	$target_stack[$user->user_no] = $user->handle_name;
      }
      ksort($target_stack);
      $target_uname = implode(' ', $target_stack);
      break;

    default:
      $target_uname = $USERS->GetHandleName($stack['target_uname'], true);
      break;
    }
    $stack_list[] = array('type' => $stack['situation'],
			  'message' =>  $uname . "\t" . $target_uname);
    $RQ_ARGS->TestItems->ability_action_list = $stack_list;
  }
  OutputAbilityAction();

  foreach($RQ_ARGS->TestItems->system_message as $date => $date_list){
    //PrintData($date_list, $date);
    foreach($date_list as $type => $type_list){
      switch($type){
      case 'FOX_EAT':
	continue 2;

      case 'VOTE_KILLED':
      case 'WOLF_KILLED':
	foreach($type_list as $handle_name){
	  $ROOM->event->rows[] = array('message' => $handle_name, 'type' => $type);
	}
	break;

      case 'WEATHER':
	if($date != $ROOM->date) continue;
	foreach($type_list as $handle_name){
	  $ROOM->event->rows[] = array('message' => $handle_name, 'type' => $type);
	}
	break;
      }
      //PrintData($type_list, $type);
      foreach($type_list as $handle_name){
	if(is_null($USERS->HandleNameToUname($handle_name)) || $handle_name === 0) continue;
	echo GenerateDeadManType($handle_name, $type);
      }
    }
  }

  $USERS->SetEvent();
  //PrintData($ROOM->event);
  echo GenerateWeatherReport();

  //$ROOM->status = 'finished';
  OutputPlayerList(); //プレイヤーリスト
  OutputAbility();
  //foreach(array(5, 18, 2, 9, 13, 14, 23) as $id){
  foreach(range(1, 25) as $id){
    $SELF = $USERS->ByID($id); OutputAbility();
  }
  //var_dump($USERS->IsOpenCast());
}while(false);
//PrintData($GAME_CONF->RateToProbability($GAME_CONF->weather_list));
//InsertLog();
//PrintData($ROLES->loaded->file);
//PrintData(array_keys($ROLES->loaded->class));
//PrintData($INIT_CONF->loaded->class);
OutputHTMLFooter(); //HTMLフッタ
