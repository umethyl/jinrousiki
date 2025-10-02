<?php
define('JINRO_ROOT', '../..');
require_once(JINRO_ROOT . '/include/init.php');
$INIT_CONF->LoadClass('ROOM_CONF', 'ICON_CONF', 'ROLES');
$INIT_CONF->LoadFile('game_vote_functions', 'game_play_functions');

//-- 仮想村データをセット --//
$INIT_CONF->LoadRequest('RequestBaseGame');
$RQ_ARGS->room_no = 94;
$RQ_ARGS->TestItems->test_room = array(
  'id' => $RQ_ARGS->room_no,
  'name' => '投票テスト村',
  'comment' => '',
  'game_option'  => 'dummy_boy full_mania chaosfull chaos_open_cast no_sub_role real_time:6:4 joker',
  'date' => 9,
  'day_night' => 'night',
  //'day_night' => 'aftergame',
  'status' => 'playing'
  //'status' => 'finished'
);
$RQ_ARGS->TestItems->test_room['game_option'] .= ' not_open_cast';
$RQ_ARGS->TestItems->test_room['game_option'] .= ' open_vote';
#$RQ_ARGS->TestItems->test_room['game_option'] .= ' quiz';
$RQ_ARGS->TestItems->is_virtual_room = true;
$RQ_ARGS->vote_times = 1;
$RQ_ARGS->TestItems->test_users = array();

$RQ_ARGS->TestItems->test_users[1] = new User();
$RQ_ARGS->TestItems->test_users[1]->uname = 'dummy_boy';
$RQ_ARGS->TestItems->test_users[1]->handle_name = '身代わり君';
$RQ_ARGS->TestItems->test_users[1]->sex = 'female';
$RQ_ARGS->TestItems->test_users[1]->role = 'unconscious';
$RQ_ARGS->TestItems->test_users[1]->live = 'dead';
$RQ_ARGS->TestItems->test_users[1]->icon_filename = '../img/dummy_boy_user_icon.jpg';
$RQ_ARGS->TestItems->test_users[1]->color = '#000000';

$RQ_ARGS->TestItems->test_users[2] = new User();
$RQ_ARGS->TestItems->test_users[2]->uname = 'light_gray';
$RQ_ARGS->TestItems->test_users[2]->handle_name = '明灰';
$RQ_ARGS->TestItems->test_users[2]->sex = 'male';
$RQ_ARGS->TestItems->test_users[2]->role = 'sirius_wolf strong_voice';
$RQ_ARGS->TestItems->test_users[2]->live = 'live';

$RQ_ARGS->TestItems->test_users[3] = new User();
$RQ_ARGS->TestItems->test_users[3]->uname = 'dark_gray';
$RQ_ARGS->TestItems->test_users[3]->handle_name = '暗灰';
$RQ_ARGS->TestItems->test_users[3]->sex = 'male';
$RQ_ARGS->TestItems->test_users[3]->role = 'possessed_wolf possessed_target[3-17]';
$RQ_ARGS->TestItems->test_users[3]->live = 'live';

$RQ_ARGS->TestItems->test_users[4] = new User();
$RQ_ARGS->TestItems->test_users[4]->uname = 'yellow';
$RQ_ARGS->TestItems->test_users[4]->handle_name = '黄色';
$RQ_ARGS->TestItems->test_users[4]->sex = 'female';
$RQ_ARGS->TestItems->test_users[4]->role = 'dummy_mage authority lovers[16] mind_friend[16]';
$RQ_ARGS->TestItems->test_users[4]->live = 'live';

$RQ_ARGS->TestItems->test_users[5] = new User();
$RQ_ARGS->TestItems->test_users[5]->uname = 'orange';
$RQ_ARGS->TestItems->test_users[5]->handle_name = 'オレンジ';
$RQ_ARGS->TestItems->test_users[5]->sex = 'female';
$RQ_ARGS->TestItems->test_users[5]->role = 'soul_mage febris[3]';
$RQ_ARGS->TestItems->test_users[5]->live = 'live';

$RQ_ARGS->TestItems->test_users[6] = new User();
$RQ_ARGS->TestItems->test_users[6]->uname = 'red';
$RQ_ARGS->TestItems->test_users[6]->handle_name = '赤';
$RQ_ARGS->TestItems->test_users[6]->sex = 'female';
$RQ_ARGS->TestItems->test_users[6]->role = 'dummy_necromancer liar';
$RQ_ARGS->TestItems->test_users[6]->live = 'dead';

$RQ_ARGS->TestItems->test_users[7] = new User();
$RQ_ARGS->TestItems->test_users[7]->uname = 'light_blue';
$RQ_ARGS->TestItems->test_users[7]->handle_name = '水色';
$RQ_ARGS->TestItems->test_users[7]->sex = 'male';
$RQ_ARGS->TestItems->test_users[7]->role = 'brownie';
$RQ_ARGS->TestItems->test_users[7]->live = 'live';

$RQ_ARGS->TestItems->test_users[8] = new User();
$RQ_ARGS->TestItems->test_users[8]->uname = 'blue';
$RQ_ARGS->TestItems->test_users[8]->handle_name = '青';
$RQ_ARGS->TestItems->test_users[8]->sex = 'male';
$RQ_ARGS->TestItems->test_users[8]->role = 'anti_voodoo psycho_infected';
$RQ_ARGS->TestItems->test_users[8]->live = 'live';

$RQ_ARGS->TestItems->test_users[9] = new User();
$RQ_ARGS->TestItems->test_users[9]->uname = 'green';
$RQ_ARGS->TestItems->test_users[9]->handle_name = '緑';
$RQ_ARGS->TestItems->test_users[9]->sex = 'female';
$RQ_ARGS->TestItems->test_users[9]->role = 'sacrifice_cat mind_open joker[4]';
$RQ_ARGS->TestItems->test_users[9]->live = 'live';

$RQ_ARGS->TestItems->test_users[10] = new User();
$RQ_ARGS->TestItems->test_users[10]->uname = 'purple';
$RQ_ARGS->TestItems->test_users[10]->handle_name = '紫';
$RQ_ARGS->TestItems->test_users[10]->sex = 'female';
$RQ_ARGS->TestItems->test_users[10]->role = 'assassin lovers[16] mind_friend[16] mind_friend[21] protected[21]';
$RQ_ARGS->TestItems->test_users[10]->live = 'live';

$RQ_ARGS->TestItems->test_users[11] = new User();
$RQ_ARGS->TestItems->test_users[11]->uname = 'cherry';
$RQ_ARGS->TestItems->test_users[11]->handle_name = 'さくら';
$RQ_ARGS->TestItems->test_users[11]->sex = 'female';
$RQ_ARGS->TestItems->test_users[11]->role = 'jammer_mad silent';
$RQ_ARGS->TestItems->test_users[11]->live = 'live';

$RQ_ARGS->TestItems->test_users[12] = new User();
$RQ_ARGS->TestItems->test_users[12]->uname = 'white';
$RQ_ARGS->TestItems->test_users[12]->handle_name = '白';
$RQ_ARGS->TestItems->test_users[12]->sex = 'male';
$RQ_ARGS->TestItems->test_users[12]->role = 'trap_mad';
$RQ_ARGS->TestItems->test_users[12]->live = 'live';

$RQ_ARGS->TestItems->test_users[13] = new User();
$RQ_ARGS->TestItems->test_users[13]->uname = 'black';
$RQ_ARGS->TestItems->test_users[13]->handle_name = '黒';
$RQ_ARGS->TestItems->test_users[13]->sex = 'male';
$RQ_ARGS->TestItems->test_users[13]->role = 'black_fox';
$RQ_ARGS->TestItems->test_users[13]->live = 'live';

$RQ_ARGS->TestItems->test_users[14] = new User();
$RQ_ARGS->TestItems->test_users[14]->uname = 'gold';
$RQ_ARGS->TestItems->test_users[14]->handle_name = '金';
$RQ_ARGS->TestItems->test_users[14]->sex = 'female';
$RQ_ARGS->TestItems->test_users[14]->role = 'phantom_fox death_warrant[4]';
$RQ_ARGS->TestItems->test_users[14]->live = 'live';

$RQ_ARGS->TestItems->test_users[15] = new User();
$RQ_ARGS->TestItems->test_users[15]->uname = 'frame';
$RQ_ARGS->TestItems->test_users[15]->handle_name = '炎';
$RQ_ARGS->TestItems->test_users[15]->sex = 'female';
$RQ_ARGS->TestItems->test_users[15]->role = 'miasma_fox';
$RQ_ARGS->TestItems->test_users[15]->live = 'live';

$RQ_ARGS->TestItems->test_users[16] = new User();
$RQ_ARGS->TestItems->test_users[16]->uname = 'scarlet';
$RQ_ARGS->TestItems->test_users[16]->handle_name = '紅';
$RQ_ARGS->TestItems->test_users[16]->sex = 'female';
$RQ_ARGS->TestItems->test_users[16]->role = 'sweet_cupid';
$RQ_ARGS->TestItems->test_users[16]->live = 'live';

$RQ_ARGS->TestItems->test_users[17] = new User();
$RQ_ARGS->TestItems->test_users[17]->uname = 'sky';
$RQ_ARGS->TestItems->test_users[17]->handle_name = '空';
$RQ_ARGS->TestItems->test_users[17]->sex = 'male';
$RQ_ARGS->TestItems->test_users[17]->role = 'executor possessed[3-3]';
$RQ_ARGS->TestItems->test_users[17]->live = 'drop';

$RQ_ARGS->TestItems->test_users[18] = new User();
$RQ_ARGS->TestItems->test_users[18]->uname = 'sea';
$RQ_ARGS->TestItems->test_users[18]->handle_name = '海';
$RQ_ARGS->TestItems->test_users[18]->sex = 'male';
$RQ_ARGS->TestItems->test_users[18]->role = 'therian_mad';
$RQ_ARGS->TestItems->test_users[18]->live = 'live';

$RQ_ARGS->TestItems->test_users[19] = new User();
$RQ_ARGS->TestItems->test_users[19]->uname = 'land';
$RQ_ARGS->TestItems->test_users[19]->handle_name = '陸';
$RQ_ARGS->TestItems->test_users[19]->sex = 'female';
$RQ_ARGS->TestItems->test_users[19]->role = 'shadow_fairy downer_voice liar';
$RQ_ARGS->TestItems->test_users[19]->live = 'live';

$RQ_ARGS->TestItems->test_users[20] = new User();
$RQ_ARGS->TestItems->test_users[20]->uname = 'rose';
$RQ_ARGS->TestItems->test_users[20]->handle_name = '薔薇';
$RQ_ARGS->TestItems->test_users[20]->sex = 'female';
$RQ_ARGS->TestItems->test_users[20]->role = 'soul_vampire';
$RQ_ARGS->TestItems->test_users[20]->live = 'live';

$RQ_ARGS->TestItems->test_users[21] = new User();
$RQ_ARGS->TestItems->test_users[21]->uname = 'peach';
$RQ_ARGS->TestItems->test_users[21]->handle_name = '桃';
$RQ_ARGS->TestItems->test_users[21]->sex = 'female';
$RQ_ARGS->TestItems->test_users[21]->role = 'dummy_mania[2] mind_friend[21]';
$RQ_ARGS->TestItems->test_users[21]->live = 'live';

$RQ_ARGS->TestItems->test_users[22] = new User();
$RQ_ARGS->TestItems->test_users[22]->uname = 'gust';
$RQ_ARGS->TestItems->test_users[22]->handle_name = '霧';
$RQ_ARGS->TestItems->test_users[22]->sex = 'male';
$RQ_ARGS->TestItems->test_users[22]->role = 'incubus_escaper';
$RQ_ARGS->TestItems->test_users[22]->live = 'live';

$RQ_ARGS->TestItems->test_users[23] = new User();
$RQ_ARGS->TestItems->test_users[23]->uname = 'cloud';
$RQ_ARGS->TestItems->test_users[23]->handle_name = '雲';
$RQ_ARGS->TestItems->test_users[23]->sex = 'male';
$RQ_ARGS->TestItems->test_users[23]->role = 'revive_priest';
$RQ_ARGS->TestItems->test_users[23]->live = 'dead';

$RQ_ARGS->TestItems->test_users[24] = new User();
$RQ_ARGS->TestItems->test_users[24]->uname = 'moon';
$RQ_ARGS->TestItems->test_users[24]->handle_name = '月';
$RQ_ARGS->TestItems->test_users[24]->sex = 'female';
$RQ_ARGS->TestItems->test_users[24]->role = 'clairvoyance_scanner watcher';
$RQ_ARGS->TestItems->test_users[24]->live = 'live';

$RQ_ARGS->TestItems->test_users[25] = new User();
$RQ_ARGS->TestItems->test_users[25]->uname = 'sun';
$RQ_ARGS->TestItems->test_users[25]->handle_name = '太陽';
$RQ_ARGS->TestItems->test_users[25]->sex = 'female';
$RQ_ARGS->TestItems->test_users[25]->role = 'saint disfavor mind_presage[24]';
$RQ_ARGS->TestItems->test_users[25]->live = 'live';
$RQ_ARGS->TestItems->test_users[25]->profile = "あーうー\nうーあー";

//$RQ_ARGS->TestItems->test_users = 30;
$icon_color_list = array('#DDDDDD', '#999999', '#FFD700', '#FF9900', '#FF0000',
			 '#99CCFF', '#0066FF', '#00EE00', '#CC00CC', '#FF9999');
foreach($RQ_ARGS->TestItems->test_users as $id => $user){
  $user->room_no = $RQ_ARGS->room_no;
  $user->user_no = $id;
  if(is_null($user->profile)) $user->profile = '';
  $user->last_load_day_night = 'night';
  $user->is_system = $user->user_no == 1;
  if($id > 1){
    $user->color = $icon_color_list[($id - 2) % 10];
    $user->icon_filename = sprintf('%03d.gif', ($id - 2) % 10 + 1);
  }
}

//-- 仮想投票データをセット --//
$RQ_ARGS->TestItems->vote->day = array();
$RQ_ARGS->TestItems->vote_target_day = array(
  array('id' =>  2, 'target_no' =>  3),
  //array('id' =>  3, 'target_no' =>  7),
  array('id' =>  3, 'target_no' => 10),
  array('id' =>  4, 'target_no' =>  3),
  array('id' =>  5, 'target_no' =>  3),
  //array('id' =>  6, 'target_no' =>  3),
  //array('id' =>  7, 'target_no' =>  3),
  array('id' =>  7, 'target_no' =>  5),
  array('id' =>  8, 'target_no' =>  9),
  array('id' =>  9, 'target_no' =>  7),
  array('id' => 10, 'target_no' =>  7),
  array('id' => 11, 'target_no' => 16),
  array('id' => 12, 'target_no' => 25),
  array('id' => 13, 'target_no' => 25),
  array('id' => 14, 'target_no' => 25),
  array('id' => 15, 'target_no' =>  7),
  array('id' => 16, 'target_no' => 24),
  //array('id' => 17, 'target_no' => 22),
  array('id' => 18, 'target_no' => 22),
  //array('id' => 18, 'target_no' => 3),
  array('id' => 19, 'target_no' => 22),
  array('id' => 20, 'target_no' => 22),
  array('id' => 21, 'target_no' => 22),
  array('id' => 22, 'target_no' =>  7),
  array('id' => 23, 'target_no' =>  8),
  array('id' => 24, 'target_no' => 25),
  #array('id' => 25, 'target_no' =>  7),
  array('id' => 25, 'target_no' => 12),
);

$RQ_ARGS->TestItems->vote->night = array(
  #array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'dummy_boy'),
  #array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'yellow'),
  array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'sea'),
  #array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'purple'),
  #array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'frame'),
  #array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'white'),
  #array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'land'),
  #array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'rose'),
  #array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'moon'),
  #array('uname' => 'light_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'sun'),
  #array('uname' => 'dark_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'yellow'),
  #array('uname' => 'dark_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'blue'),
  #array('uname' => 'dark_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'cherry'),
  #array('uname' => 'dark_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'land'),
  #array('uname' => 'dark_gray', 'situation' => 'WOLF_EAT', 'target_uname' => 'sun'),
  #array('uname' => 'yellow', 'situation' => 'MAGE_DO', 'target_uname' => 'light_gray'),
  #array('uname' => 'yellow', 'situation' => 'MAGE_DO', 'target_uname' => 'dark_gray'),
  #array('uname' => 'yellow', 'situation' => 'MAGE_DO', 'target_uname' => 'light_blue'),
  array('uname' => 'yellow', 'situation' => 'MAGE_DO', 'target_uname' => 'gold'),
  #array('uname' => 'orange', 'situation' => 'MAGE_DO', 'target_uname' => 'blue'),
  array('uname' => 'orange', 'situation' => 'MAGE_DO', 'target_uname' => 'cloud'),
  #array('uname' => 'orange', 'situation' => 'MAGE_DO', 'target_uname' => 'moon'),
  #array('uname' => 'orange', 'situation' => 'MAGE_DO', 'target_uname' => 'sun'),
  #array('uname' => 'light_blue', 'situation' => 'GUARD_DO', 'target_uname' => 'dark_gray'),
  #array('uname' => 'light_blue', 'situation' => 'GUARD_DO', 'target_uname' => 'yellow'),
  #array('uname' => 'light_blue', 'situation' => 'GUARD_DO', 'target_uname' => 'red'),
  #array('uname' => 'light_blue', 'situation' => 'GUARD_DO', 'target_uname' => 'cherry'),
  #array('uname' => 'light_blue', 'situation' => 'GUARD_DO', 'target_uname' => 'white'),
  #array('uname' => 'light_blue', 'situation' => 'GUARD_DO', 'target_uname' => 'rose'),
  #array('uname' => 'light_blue', 'situation' => 'GUARD_DO', 'target_uname' => 'sun'),
  #array('uname' => 'blue', 'situation' => 'ANTI_VOODOO_DO', 'target_uname' => 'dark_gray'),
  array('uname' => 'blue', 'situation' => 'ANTI_VOODOO_DO', 'target_uname' => 'light_blue'),
  #array('uname' => 'blue', 'situation' => 'ANTI_VOODOO_DO', 'target_uname' => 'gold'),
  #array('uname' => 'blue', 'situation' => 'ANTI_VOODOO_DO', 'target_uname' => 'sun'),
  #array('uname' => 'green', 'situation' => 'POISON_CAT_DO', 'target_uname' => 'red'),
  #array('uname' => 'green', 'situation' => 'POISON_CAT_DO', 'target_uname' => 'moon'),
  array('uname' => 'green', 'situation' => 'POISON_CAT_NOT_DO', 'target_uname' => NULL),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'yellow'),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'light_gray'),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'dark_gray'),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'orange'),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'light_blue'),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'blue'),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'white'),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'gold'),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'cloud'),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'moon'),
  #array('uname' => 'purple', 'situation' => 'ASSASSIN_DO', 'target_uname' => 'dark_gray'),
  array('uname' => 'purple', 'situation' => 'ASSASSIN_NOT_DO', 'target_uname' => NULL),
  #array('uname' => 'cherry', 'situation' => 'JAMMER_MAD_DO', 'target_uname' => 'yellow'),
  #array('uname' => 'cherry', 'situation' => 'JAMMER_MAD_DO', 'target_uname' => 'black'),
  array('uname' => 'cherry', 'situation' => 'JAMMER_MAD_DO', 'target_uname' => 'gust'),
  #array('uname' => 'cherry', 'situation' => 'VOODOO_MAD_DO', 'target_uname' => 'yellow'),
  #array('uname' => 'cherry', 'situation' => 'DREAM_EAT', 'target_uname' => 'yellow'),
  #array('uname' => 'cherry', 'situation' => 'DREAM_EAT', 'target_uname' => 'light_blue'),
  #array('uname' => 'white', 'situation' => 'TRAP_MAD_DO',	'target_uname' => 'yellow'),
  #array('uname' => 'white', 'situation' => 'TRAP_MAD_DO',	'target_uname' => 'purple'),
  array('uname' => 'white', 'situation' => 'TRAP_MAD_DO',	'target_uname' => 'green'),
  #array('uname' => 'white', 'situation' => 'TRAP_MAD_DO',	'target_uname' => 'white'),
  #array('uname' => 'white', 'situation' => 'TRAP_MAD_NOT_DO',	'target_uname' => NULL),
  #array('uname' => 'white', 'situation' => 'POSSESSED_DO',	'target_uname' => 'light_blue'),
  #array('uname' => 'white', 'situation' => 'POSSESSED_NOT_DO',	'target_uname' => NULL),
  #array('uname' => 'black', 'situation' => 'MAGE_DO', 'target_uname' => 'light_blue'),
  #array('uname' => 'gold', 'situation' => 'VOODOO_FOX_DO',	'target_uname' => 'light_blue'),
  #array('uname' => 'gold', 'situation' => 'POSSESSED_DO',	'target_uname' => 'dark_gray'),
  #array('uname' => 'gold', 'situation' => 'POSSESSED_DO',	'target_uname' => 'moon'),
  #array('uname' => 'gold', 'situation' => 'POSSESSED_NOT_DO',	'target_uname' => NULL),
  #array('uname' => 'gold', 'situation' => 'POISON_CAT_DO',	'target_uname' => 'red'),
  #array('uname' => 'gold', 'situation' => 'POISON_CAT_NOT_DO',	'target_uname' => NULL),
  #array('uname' => 'frame', 'situation' => 'CHILD_FOX_DO',	'target_uname' => 'purple'),
  #array('uname' => 'frame', 'situation' => 'JAMMER_MAD_DO',	'target_uname' => 'orange'),
  #array('uname' => 'scarlet', 'situation' => 'CUPID_DO', 'target_uname' => 'scarlet sea'),
  #array('uname' => 'sky', 'situation' => 'CUPID_DO', 'target_uname' => 'orange purple'),
  #array('uname' => 'sea', 'situation' => 'FAIRY_DO', 'target_uname' => 'gust'),
  #array('uname' => 'land', 'situation' => 'FAIRY_DO', 'target_uname' => 'yellow'),
  #array('uname' => 'land', 'situation' => 'FAIRY_DO', 'target_uname' => 'yellow gold'),
  array('uname' => 'land', 'situation' => 'FAIRY_DO', 'target_uname' => 'rose'),
  array('uname' => 'rose', 'situation' => 'VAMPIRE_DO', 'target_uname' => 'cloud'),
  #array('uname' => 'peach', 'situation' => 'MANIA_DO', 'target_uname' => 'purple'),
  #array('uname' => 'gust', 'situation' => 'ESCAPE_DO', 'target_uname' => 'yellow'),
  array('uname' => 'gust', 'situation' => 'ESCAPE_DO', 'target_uname' => 'green'),
  #array('uname' => 'gust', 'situation' => 'ESCAPE_DO', 'target_uname' => 'white'),
  #array('uname' => 'gust', 'situation' => 'ESCAPE_DO', 'target_uname' => 'rose'),
  #array('uname' => 'gust', 'situation' => 'FAIRY_DO', 'target_uname' => 'rose'),
  #array('uname' => 'gust', 'situation' => 'TRAP_MAD_DO', 'target_uname' => 'gust'),
  #array('uname' => 'gust', 'situation' => 'OGRE_DO', 'target_uname' => 'moon'),
  #array('uname' => 'gust', 'situation' => 'OGRE_NOT_DO', 'target_uname' => NULL),
  #array('uname' => 'cloud', 'situation' => 'REPORTER_DO', 'target_uname' => 'light_gray'),
  #array('uname' => 'cloud', 'situation' => 'REPORTER_DO', 'target_uname' => 'yellow'),
  #array('uname' => 'cloud', 'situation' => 'REPORTER_DO', 'target_uname' => 'black'),
  #array('uname' => 'cloud', 'situation' => 'REPORTER_DO', 'target_uname' => 'moon'),
  #array('uname' => 'cloud', 'situation' => 'ESCAPE_DO', 'target_uname' => 'light_gray'),
  #array('uname' => 'cloud', 'situation' => 'ESCAPE_DO', 'target_uname' => 'purple'),
  #array('uname' => 'cloud', 'situation' => 'REPORTER_DO', 'target_uname' => 'gold'),
  array('uname' => 'moon', 'situation' => 'MIND_SCANNER_DO', 'target_uname' => 'light_gray'),
  #array('uname' => 'moon', 'situation' => 'MIND_SCANNER_DO', 'target_uname' => 'orange'),
  #array('uname' => 'moon', 'situation' => 'MIND_SCANNER_DO', 'target_uname' => 'sky'),
  #array('uname' => 'sun', 'situation' => 'TRAP_MAD_DO', 'target_uname' => 'yellow'),
  #array('uname' => 'sun', 'situation' => 'TRAP_MAD_DO', 'target_uname' => 'white'),
  #array('uname' => 'sun', 'situation' => 'TRAP_MAD_DO', 'target_uname' => 'gust'),
  array('uname' => 'sun', 'situation' => 'OGRE_DO', 'target_uname' => 'light_gray'),
  #array('uname' => 'sun', 'situation' => 'OGRE_DO', 'target_uname' => 'moon'),
  #array('uname' => 'sun', 'situation' => 'OGRE_NOT_DO', 'target_uname' => NULL),
);

//-- 仮想システムメッセージをセット --//
$RQ_ARGS->TestItems->system_message = array();

//-- 仮想イベントをセット --//
$RQ_ARGS->TestItems->event = array(
  #array('type' => 'VOTE_KILLED', 'message' => 'light_gray'),
  #array('type' => 'WOLF_KILLED', 'message' => 'dummy_boy'),
);

//-- データ収集 --//
//$DB_CONF->Connect(); // DB 接続 (必要になったらセットする)
$ROOM = new Room($RQ_ARGS); //村情報を取得
$ROOM->test_mode = true;
$ROOM->log_mode = true;
$ROOM->date = 3;
#$ROOM->day_night = 'beforegame';
#$ROOM->day_night = 'day';
$ROOM->day_night = 'night';
#$ROOM->day_night = 'aftergame';
//$ROOM->system_time = TZTime(); //現在時刻を取得
$role_view_mode = false;

$USERS = new UserDataSet($RQ_ARGS); //ユーザ情報をロード
#foreach($USERS->rows as $user) $user->live = 'live';
#$USERS->ByID(9)->live = 'live';
#$SELF = new User();
$SELF = $USERS->ByID(1);
#$SELF = $USERS->ByID(3);
#$SELF = $USERS->TraceExchange(14);

//-- データ出力 --//
OutputHTMLHeader('投票テスト', 'game'); //HTMLヘッダ
if($role_view_mode){
  foreach(array_keys($ROLE_DATA->main_role_list) as $role) $ROLE_IMG->Output($role);
  foreach(array_keys($ROLE_DATA->sub_role_list)  as $role) $ROLE_IMG->Output($role);
  OutputHTMLFooter(true);
}
//OutputGameOption($ROOM->game_option, '');
OutputPlayerList(); //プレイヤーリスト
#PrintData($ROOM);
#PrintData($ROOM->event);
#PrintData($USERS);
#PrintData($SELF);
#PrintData($USERS->ByID(8));
#PrintData($USERS->ByID(22)->GetCamp());
OutputAbility();

if($ROOM->IsDay()){ //昼の投票テスト
  $self_id = $SELF->user_no;
  $RQ_ARGS->situation = 'VOTE_KILL';
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
  $ROOM->day_night = 'night';
  $SELF = $USERS->ByID($self_id);
}
elseif($ROOM->IsNight()){ // 夜の投票テスト
  //PrintData($RQ_ARGS->TestItems->vote->night);
  AggregateVoteNight();
  $ROOM->date++;
  $ROOM->day_night = 'day';
}
//PrintData($RQ_ARGS->TestItems->system_message);

do{
  //break;
  foreach($USERS->rows as $user){
    $user->live = $user->IsLive(true) ? 'live' : 'dead';
    $user->ReparseRoles();
  }

  foreach($RQ_ARGS->TestItems->vote->night as $stack){
    $uname = $USERS->GetHandleName($stack['uname'], true);
    $target_uname = $USERS->GetHandleName($stack['target_uname'], true);
    $stack_list[] = array('type' => $stack['situation'],
			  'message' =>  $uname . "\t" . $target_uname);
    $RQ_ARGS->TestItems->ablity_action_list = $stack_list;
  }
  OutputAbilityAction();

  foreach($RQ_ARGS->TestItems->system_message as $date => $date_list){
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
  $ROOM->status = 'finished';
  OutputPlayerList(); //プレイヤーリスト
  OutputAbility();
  foreach(array(5, 23, 25) as $id){
    $SELF = $USERS->ByID($id); OutputAbility();
  }
  #var_dump($USERS->IsOpenCast());
}while(false);
OutputHTMLFooter(); //HTMLフッタ
