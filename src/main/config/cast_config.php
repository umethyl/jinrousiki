<?php
//-- 配役設定 --//
class CastConfig extends CastConfigBase{
  //-- 配役テーブル --//
  /* 設定の見方
    [ゲーム参加人数] => array([配役名1] => [配役名1の人数], [配役名2] => [配役名2の人数], ...),
    ゲーム参加人数と配役名の人数の合計が合わない場合はゲーム開始投票時にエラーが返る
  */
  var $role_list = array(
     4 => array('human' =>  1, 'wolf' => 1, 'mage' => 1, 'mad' => 1),
     5 => array('wolf' =>   1, 'mage' => 2, 'mad' => 2),
     6 => array('human' =>  1, 'wolf' => 1, 'mage' => 1, 'poison' => 1, 'fox' => 1, 'cupid' => 1),
     7 => array('human' =>  3, 'wolf' => 1, 'mage' => 1, 'guard' => 1, 'fox' => 1),
     8 => array('human' =>  5, 'wolf' => 2, 'mage' => 1),
     9 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1),
    10 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1),
    11 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1),
    12 => array('human' =>  6, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1),
    13 => array('human' =>  5, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common'=> 2),
    14 => array('human' =>  6, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2),
    15 => array('human' =>  6, 'wolf' => 2, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    16 => array('human' =>  6, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    17 => array('human' =>  7, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    18 => array('human' =>  8, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    19 => array('human' =>  9, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    20 => array('human' => 10, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    21 => array('human' => 11, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    22 => array('human' => 12, 'wolf' => 3, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    23 => array('human' => 12, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    24 => array('human' => 13, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    25 => array('human' => 14, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    26 => array('human' => 15, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 1),
    27 => array('human' => 15, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 2),
    28 => array('human' => 14, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 2, 'guard' => 1, 'common' => 3, 'fox' => 2),
    29 => array('human' => 15, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 2, 'guard' => 1, 'common' => 3, 'fox' => 2),
    30 => array('human' => 16, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 2, 'guard' => 1, 'common' => 3, 'fox' => 2),
    31 => array('human' => 17, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 2, 'guard' => 1, 'common' => 3, 'fox' => 2),
    32 => array('human' => 16, 'wolf' => 5, 'mage' => 1, 'necromancer' => 1, 'mad' => 2, 'guard' => 2, 'common' => 3, 'fox' => 2),
    33 => array('human' => 32, 'wolf' => 1),
    34 => array('human' => 33, 'wolf' => 1),
    35 => array('human' => 34, 'wolf' => 1),
    36 => array('human' => 35, 'wolf' => 1),
    37 => array('human' => 36, 'wolf' => 1),
    38 => array('human' => 37, 'wolf' => 1),
    39 => array('human' => 38, 'wolf' => 1),
    40 => array('human' => 39, 'wolf' => 1),
    41 => array('human' => 40, 'wolf' => 1),
    42 => array('human' => 41, 'wolf' => 1),
    43 => array('human' => 42, 'wolf' => 1),
    44 => array('human' => 43, 'wolf' => 1),
    45 => array('human' => 44, 'wolf' => 1),
    46 => array('human' => 45, 'wolf' => 1),
    47 => array('human' => 46, 'wolf' => 1),
    48 => array('human' => 47, 'wolf' => 1),
    49 => array('human' => 48, 'wolf' => 1),
    50 => array('human' => 49, 'wolf' => 1)
                         );
  //-- 役職出現人数 --//
  //各役職の出現に必要な人数を設定する
  var $poison         = 20; //埋毒者 [村人2 → 埋毒者1、人狼1]
  var $assassin       = 22; //暗殺者 [村人2 → 暗殺者1、人狼1]
  var $boss_wolf      = 18; //白狼 [人狼1 → 白狼]
  var $poison_wolf    = 20; //毒狼 (+ 薬師) [人狼1 → 毒狼1、村人1 → 薬師1]
  var $possessed_wolf = 17; //憑狼 [人狼1 → 憑狼1]
  var $sirius_wolf    = 17; //天狼 [人狼1 → 天狼1]
  var $cupid          = 16; //キューピッド [村人1 → キューピッド1]
  var $medium         = 20; //巫女 (+ 女神) [村人2 → 巫女1、女神1]
  var $mania          = 16; //神話マニア [村人1 → 神話マニア1]
  var $decide         = 16; //決定者 [兼任]
  var $authority      = 16; //権力者 [兼任]

  //希望制で役職希望が通る確率 (%) (身代わり君がいる場合は 100% にしても保証されません)
  var $wish_role_rate = 100;

  //身代わり君がならない役職グループのリスト (人狼・妖狐陣営は常時対象外なので設定不要)
  var $disable_dummy_boy_role_list = array('poison');

  //-- 真・闇鍋の配役設定 --//
  //固定配役 (普通闇鍋)
  var $chaos_fix_role_list = array('mage' => 1, 'wolf' => 1);

  //固定配役 (真・闇鍋)
  var $chaosfull_fix_role_list = array('mage' => 1, 'wolf' => 1);

  //固定配役 (超・闇鍋)
  var $chaos_hyper_fix_role_list = array('mage' => 1, 'wolf' => 1);

  //人狼の最低出現枠 (役職名 => 出現比)
  //普通闇鍋
  var $chaos_wolf_list = array(
    'wolf'           => 60,
    'boss_wolf'      =>  5,
    'poison_wolf'    => 10,
    'tongue_wolf'    =>  5,
    'silver_wolf'    => 20);

  //真・闇鍋
  var $chaosfull_wolf_list = array(
    'wolf'           => 60,
    'boss_wolf'      =>  5,
    'cursed_wolf'    =>  5,
    'poison_wolf'    =>  5,
    'resist_wolf'    =>  5,
    'tongue_wolf'    =>  5,
    'cute_wolf'      => 10,
    'silver_wolf'    =>  5);

  //超・闇鍋
  var $chaos_hyper_wolf_list = array(
    'wolf'           => 40,
    'boss_wolf'      =>  2,
    'gold_wolf'      =>  3,
    'phantom_wolf'   =>  2,
    'cursed_wolf'    =>  2,
    'wise_wolf'      =>  3,
    'poison_wolf'    =>  3,
    'resist_wolf'    =>  3,
    'blue_wolf'      =>  2,
    'emerald_wolf'   =>  2,
    'sex_wolf'       =>  2,
    'tongue_wolf'    =>  2,
    'possessed_wolf' =>  2,
    'hungry_wolf'    =>  2,
    'doom_wolf'      =>  2,
    'sirius_wolf'    =>  2,
    'elder_wolf'     =>  3,
    'cute_wolf'      => 10,
    'scarlet_wolf'   =>  3,
    'silver_wolf'    => 10);

  //妖狐の最低出現枠 (役職名 => 出現比)
  //普通闇鍋
  var $chaos_fox_list = array(
    'fox'           => 90,
    'child_fox'     => 10);

  //真・闇鍋
  var $chaosfull_fox_list = array(
    'fox'           => 80,
    'white_fox'     =>  3,
    'poison_fox'    =>  3,
    'voodoo_fox'    =>  3,
    'cursed_fox'    =>  3,
    'silver_fox'    =>  3,
    'child_fox'     =>  5);

  //超・闇鍋
  var $chaos_hyper_fox_list = array(
    'fox'           => 40,
    'white_fox'     =>  2,
    'black_fox'     =>  2,
    'gold_fox'      =>  3,
    'phantom_fox'   =>  2,
    'poison_fox'    =>  3,
    'blue_fox'      =>  2,
    'emerald_fox'   =>  2,
    'voodoo_fox'    =>  2,
    'revive_fox'    =>  2,
    'possessed_fox' =>  2,
    'doom_fox'      =>  2,
    'cursed_fox'    =>  2,
    'elder_fox'     =>  3,
    'cute_fox'      =>  5,
    'scarlet_fox'   =>  3,
    'silver_fox'    =>  3,
    'child_fox'     =>  8,
    'sex_fox'       =>  4,
    'stargazer_fox' =>  2,
    'jammer_fox'    =>  2,
    'miasma_fox'    =>  2,
    'howl_fox'      =>  2);

  //ランダム配役テーブル (役職名 => 出現比)
  //普通闇鍋
  var $chaos_random_role_list = array(
    'human'              => 88,
    'mage'               => 50,
    'soul_mage'          =>  5,
    'psycho_mage'        => 10,
    'necromancer'        => 60,
    'medium'             => 30,
    'guard'              => 70,
    'poison_guard'       =>  5,
    'reporter'           => 15,
    'common'             => 75,
    'poison'             => 40,
    'incubate_poison'    => 10,
    'pharmacist'         => 20,
    'assassin'           => 20,
    'doll'               => 20,
    'doll_master'        => 10,
    'escaper'            => 30,
    'wolf'               => 80,
    'boss_wolf'          => 10,
    'poison_wolf'        => 40,
    'tongue_wolf'        => 20,
    'silver_wolf'        => 30,
    'mad'                => 60,
    'fanatic_mad'        => 20,
    'whisper_mad'        => 10,
    'fox'                => 50,
    'child_fox'          => 20,
    'cupid'              => 30,
    'self_cupid'         => 10,
    'quiz'               =>  2,
    'chiroptera'         => 50,
    'mania'              => 10);

  //真・闇鍋
  var $chaosfull_random_role_list = array(
    'human'              =>  3,
    'suspect'            => 15,
    'unconscious'        => 20,
    'mage'               => 20,
    'soul_mage'          =>  5,
    'psycho_mage'        => 10,
    'sex_mage'           => 15,
    'voodoo_killer'      => 10,
    'dummy_mage'         => 15,
    'necromancer'        => 40,
    'soul_necromancer'   =>  5,
    'yama_necromancer'   => 10,
    'dummy_necromancer'  => 25,
    'medium'             => 30,
    'guard'              => 40,
    'poison_guard'       =>  5,
    'reporter'           => 10,
    'anti_voodoo'        => 15,
    'dummy_guard'        => 20,
    'common'             => 80,
    'dummy_common'       => 10,
    'poison'             => 20,
    'strong_poison'      =>  5,
    'incubate_poison'    => 10,
    'dummy_poison'       => 15,
    'poison_cat'         => 10,
    'pharmacist'         => 30,
    'assassin'           => 20,
    'mind_scanner'       => 20,
    'jealousy'           => 15,
    'wolf'               => 75,
    'boss_wolf'          => 10,
    'cursed_wolf'        => 10,
    'poison_wolf'        => 15,
    'resist_wolf'        => 15,
    'tongue_wolf'        => 20,
    'cute_wolf'          => 30,
    'silver_wolf'        => 15,
    'mad'                => 20,
    'fanatic_mad'        => 10,
    'whisper_mad'        =>  5,
    'jammer_mad'         => 10,
    'voodoo_mad'         => 10,
    'dream_eater_mad'    => 10,
    'trap_mad'           => 10,
    'corpse_courier_mad' => 15,
    'fox'                => 30,
    'white_fox'          => 10,
    'poison_fox'         =>  7,
    'voodoo_fox'         =>  5,
    'cursed_fox'         =>  3,
    'silver_fox'         =>  5,
    'child_fox'          => 10,
    'cupid'              => 25,
    'self_cupid'         => 10,
    'mind_cupid'         =>  5,
    'quiz'               =>  2,
    'chiroptera'         => 20,
    'poison_chiroptera'  =>  5,
    'cursed_chiroptera'  =>  5,
    'mania'              => 20,
    'unknown_mania'      => 10);

  //超・闇鍋
  var $chaos_hyper_random_role_list = array(
    'human'                =>  1,
    'saint'                =>  2,
    'executor'             =>  2,
    'elder'                =>  3,
    'scripter'             =>  2,
    'suspect'              =>  2,
    'unconscious'          =>  2,
    'mage'                 => 15,
    'soul_mage'            =>  5,
    'psycho_mage'          => 10,
    'sex_mage'             => 10,
    'stargazer_mage'       =>  5,
    'voodoo_killer'        => 10,
    'dummy_mage'           => 10,
    'necromancer'          => 25,
    'soul_necromancer'     =>  5,
    'attempt_necromancer'  =>  5,
    'yama_necromancer'     => 10,
    'dummy_necromancer'    => 10,
    'medium'               =>  7,
    'bacchus_medium'       =>  5,
    'seal_medium'          =>  5,
    'revive_medium'        =>  3,
    'priest'               =>  4,
    'bishop_priest'        =>  4,
    'dowser_priest'        =>  3,
    'high_priest'          =>  3,
    'crisis_priest'        =>  4,
    'revive_priest'        => 10,
    'border_priest'        =>  3,
    'dummy_priest'         =>  4,
    'guard'                => 20,
    'hunter_guard'         => 10,
    'blind_guard'          =>  5,
    'reflect_guard'        =>  5,
    'poison_guard'         =>  5,
    'fend_guard'           =>  5,
    'reporter'             => 10,
    'anti_voodoo'          => 15,
    'dummy_guard'          => 15,
    'common'               => 50,
    'detective_common'     =>  5,
    'trap_common'          =>  5,
    'ghost_common'         =>  5,
    'dummy_common'         => 10,
    'poison'               => 15,
    'strong_poison'        =>  5,
    'incubate_poison'      => 10,
    'guide_poison'         =>  5,
    'chain_poison'         =>  5,
    'dummy_poison'         => 10,
    'poison_cat'           =>  6,
    'revive_cat'           =>  3,
    'sacrifice_cat'        =>  3,
    'eclipse_cat'          =>  3,
    'pharmacist'           =>  8,
    'cure_pharmacist'      =>  4,
    'revive_pharmacist'    =>  4,
    'alchemy_pharmacist'   =>  4,
    'assassin'             =>  4,
    'doom_assassin'        =>  4,
    'reverse_assassin'     =>  3,
    'soul_assassin'        =>  3,
    'eclipse_assassin'     =>  3,
    'mind_scanner'         =>  5,
    'evoke_scanner'        =>  3,
    'presage_scanner'      =>  3,
    'clairvoyance_scanner' =>  3,
    'whisper_scanner'      =>  2,
    'howl_scanner'         =>  2,
    'telepath_scanner'     =>  2,
    'jealousy'             =>  3,
    'divorce_jealousy'     =>  3,
    'priest_jealousy'      =>  3,
    'poison_jealousy'      =>  3,
    'brownie'              =>  2,
    'history_brownie'      =>  2,
    'revive_brownie'       =>  2,
    'cursed_brownie'       =>  2,
    'doll'                 =>  5,
    'friend_doll'          =>  3,
    'phantom_doll'         =>  2,
    'poison_doll'          =>  2,
    'doom_doll'            =>  2,
    'revive_doll'          =>  2,
    'scarlet_doll'         =>  2,
    'silver_doll'          =>  2,
    'doll_master'          => 10,
    'escaper'              =>  3,
    'incubus_escaper'      =>  2,
    'wolf'                 => 10,
    'boss_wolf'            =>  5,
    'gold_wolf'            => 10,
    'phantom_wolf'         => 10,
    'cursed_wolf'          =>  5,
    'wise_wolf'            => 10,
    'poison_wolf'          => 15,
    'resist_wolf'          => 15,
    'blue_wolf'            => 10,
    'emerald_wolf'         => 10,
    'sex_wolf'             =>  5,
    'tongue_wolf'          => 10,
    'possessed_wolf'       => 10,
    'hungry_wolf'          =>  5,
    'doom_wolf'            =>  5,
    'sirius_wolf'          =>  5,
    'elder_wolf'           => 10,
    'cute_wolf'            => 10,
    'scarlet_wolf'         => 10,
    'silver_wolf'          => 10,
    'mad'                  => 10,
    'fanatic_mad'          =>  5,
    'whisper_mad'          =>  5,
    'jammer_mad'           =>  5,
    'voodoo_mad'           =>  5,
    'enchant_mad'          =>  5,
    'dream_eater_mad'      => 10,
    'possessed_mad'        =>  5,
    'trap_mad'             => 10,
    'snow_trap_mad'        =>  5,
    'corpse_courier_mad'   =>  5,
    'amaze_mad'            =>  5,
    'agitate_mad'          =>  5,
    'miasma_mad'           =>  5,
    'therian_mad'          =>  5,
    'fox'                  =>  3,
    'white_fox'            =>  3,
    'black_fox'            =>  3,
    'gold_fox'             =>  3,
    'phantom_fox'          =>  3,
    'poison_fox'           =>  3,
    'blue_fox'             =>  3,
    'emerald_fox'          =>  3,
    'voodoo_fox'           =>  3,
    'revive_fox'           =>  3,
    'possessed_fox'        =>  3,
    'doom_fox'             =>  3,
    'cursed_fox'           =>  2,
    'elder_fox'            =>  3,
    'cute_fox'             =>  3,
    'scarlet_fox'          =>  3,
    'silver_fox'           =>  3,
    'child_fox'            =>  5,
    'sex_fox'              =>  3,
    'stargazer_fox'        =>  3,
    'jammer_fox'           =>  3,
    'miasma_fox'           =>  3,
    'howl_fox'             =>  3,
    'cupid'                =>  5,
    'self_cupid'           =>  4,
    'moon_cupid'           =>  3,
    'mind_cupid'           =>  3,
    'sweet_cupid'          =>  2,
    'triangle_cupid'       =>  2,
    'angel'                =>  4,
    'rose_angel'           =>  4,
    'lily_angel'           =>  4,
    'exchange_angel'       =>  3,
    'ark_angel'            =>  3,
    'sacrifice_angel'      =>  3,
    'quiz'                 =>  2,
    'vampire'              =>  4,
    'incubus_vampire'      =>  2,
    'succubus_vampire'     =>  2,
    'doom_vampire'         =>  2,
    'sacrifice_vampire'    =>  2,
    'soul_vampire'         =>  2,
    'chiroptera'           =>  2,
    'poison_chiroptera'    =>  2,
    'cursed_chiroptera'    =>  2,
    'boss_chiroptera'      =>  2,
    'elder_chiroptera'     =>  2,
    'scarlet_chiroptera'   =>  2,
    'dummy_chiroptera'     =>  2,
    'fairy'                =>  2,
    'spring_fairy'         =>  2,
    'summer_fairy'         =>  2,
    'autumn_fairy'         =>  2,
    'winter_fairy'         =>  2,
    'flower_fairy'         =>  2,
    'star_fairy'           =>  2,
    'sun_fairy'            =>  2,
    'moon_fairy'           =>  2,
    'grass_fairy'          =>  2,
    'light_fairy'          =>  1,
    'dark_fairy'           =>  1,
    'shadow_fairy'         =>  1,
    'ice_fairy'            =>  1,
    'mirror_fairy'         =>  1,
    'ogre'                 =>  2,
    'orange_ogre'          =>  2,
    'indigo_ogre'          =>  2,
    'poison_ogre'          =>  1,
    'west_ogre'            =>  1,
    'east_ogre'            =>  1,
    'north_ogre'           =>  1,
    'south_ogre'           =>  1,
    'incubus_ogre'         =>  1,
    'power_ogre'           =>  1,
    'revive_ogre'          =>  1,
    'sacrifice_ogre'       =>  1,
    'yaksa'                =>  2,
    'succubus_yaksa'       =>  1,
    'dowser_yaksa'         =>  1,
    'mania'                =>  3,
    'trick_mania'          =>  3,
    'soul_mania'           =>  2,
    'dummy_mania'          =>  2,
    'unknown_mania'        =>  3,
    'sacrifice_mania'      =>  2);

  var $chaos_min_wolf_rate = 10; //人狼の最小出現比 (総人口 / N)
  var $chaos_min_fox_rate  = 15; //妖狐の最小出現比 (総人口 / N)

  //役職グループの最大出現比率 (グループ => 最大人口比率)
  var $chaos_role_group_rate_list = array(
    'wolf' => 0.21, 'mad' => 0.12, 'fox' => 0.1, 'child_fox' => 0.08,
    'mage' => 0.18, 'necromancer' => 0.15, 'medium' => 0.1, 'priest' => 0.1, 'guard' => 0.15,
    'common' => 0.17, 'poison' => 0.13, 'cat' => 0.1, 'pharmacist' => 0.15, 'assassin' => 0.15,
    'scanner' => 0.15, 'jealousy' => 0.1, 'doll' => 0.15, 'escaper' => 0.15,
    'cupid' => 0.1, 'angel' => 0.07, 'quiz' => 0.15, 'vampire' => 0.15,
    'chiroptera' => 0.12, 'fairy' => 0.12, 'ogre' => 0.12, 'yaksa' => 0.12);

  //村人の出現上限補正
  var $chaos_max_human_rate = 0.1; //村人の最大人口比 (1.0 = 100%)
  //村人から振り返る役職 => 出現比
  //普通闇鍋
  var $chaos_replace_human_role_list = array('mania' => 1);

  //真・闇鍋
  var $chaosfull_replace_human_role_list = array('mania' => 7, 'unknown_mania' => 3);

  //超・闇鍋
  var $chaos_hyper_replace_human_role_list = array(
    'mania' => 35, 'trick_mania' => 20, 'soul_mania' => 10, 'dummy_mania' => 10,
    'unknown_mania' => 15, 'sacrifice_mania' => 10);

  //固定配役追加モード
  /*
    fix    : 固定枠
    random : ランダム枠 (各配列の中身は役職：出現比)
    count  : ランダム出現数 (ランダム枠毎の出現数)

    例)
    doll_master が +1, [doll:poison_doll = 1:1] の割合でランダムに +1,
    [scarlet_doll:silver_doll = 5:1] の割合でランダムに +2
    'a' => array('fix'    => array('doll_master' => 1),
		 'random' => array(array('doll'  => 1, 'poison_doll' => 1),
				   array('scarlet_doll' => 5, 'silver_doll' => 1)),
		 'count'  => array(1, 2)),
  */
  var $topping_list = array(
    'a' => array('fix' => array('doll_master' => 1),
		 'random' => array(
                    array('doll'         =>  40,
			  'friend_doll'  =>   5,
			  'phantom_doll' =>  10,
			  'poison_doll'  =>  10,
			  'doom_doll'    =>  10,
			  'revive_doll'  =>  10,
			  'scarlet_doll' =>  10,
			  'silver_doll'  =>   5)),
		 'count'  => array(1)),
    'b' => array('fix' => array('quiz' => 1, 'poison_ogre' => 1)),
    'c' => array('random' => array(
                   array('vampire'           => 4,
			 'incubus_vampire'   => 1,
			 'succubus_vampire'  => 1,
			 'doom_vampire'      => 1,
			 'sacrifice_vampire' => 2,
			 'soul_vampire'      => 1)),
		 'count' => array(1)),
    'd' => array('fix' => array('resist_wolf' => 1),
		 'random' => array(
                    array('poison_cat'    =>  4,
			  'revive_cat'    =>  2,
			  'sacrifice_cat' =>  2,
			  'eclipse_cat'   =>  2)),
		 'count'  => array(1)),
    'e' => array('fix' => array('anti_voodoo' => 1, 'possessed_wolf' => 1)),
    'f' => array('random' => array(
                   array('ogre'           =>  2,
			 'orange_ogre'    =>  2,
			 'indigo_ogre'    =>  2,
			 'poison_ogre'    =>  1,
			 'west_ogre'      =>  1,
			 'east_ogre'      =>  1,
			 'north_ogre'     =>  1,
			 'south_ogre'     =>  1,
			 'incubus_ogre'   =>  1,
			 'power_ogre'     =>  1,
			 'revive_ogre'    =>  1,
			 'sacrifice_ogre' =>  1,
			 'yaksa'          =>  2,
			 'succubus_yaksa' =>  1,
			 'dowser_yaksa'   =>  1)),
		 'count' => array(2)),
			    );

  //サブ役職制限：EASYモード
  var $chaos_sub_role_limit_easy_list = array(
    'authority', 'critical_voter', 'random_voter', 'rebel', 'watcher', 'decide', 'plague',
    'good_luck', 'bad_luck');

  //サブ役職制限：NORMALモード
  var $chaos_sub_role_limit_normal_list = array(
    'authority', 'critical_voter', 'random_voter', 'rebel', 'watcher', 'decide', 'plague',
    'good_luck', 'bad_luck', 'upper_luck', 'downer_luck', 'star', 'disfavor', 'critical_luck',
    'random_luck', 'strong_voice', 'normal_voice', 'weak_voice', 'upper_voice', 'downer_voice',
    'inside_voice', 'outside_voice', 'random_voice');

  //お祭り村専用配役テーブル
  var $festival_role_list = array(
     8 => array('human' => 2, 'mage' => 1, 'necromancer' => 1, 'wolf' => 1, 'mad' => 1, 'whisper_mad' => 1, 'fox' => 1),
     9 => array('human' => 3 , 'guard' => 3, 'wolf' => 2, 'chiroptera' => 1),
    10 => array('human' => 2, 'mage' => 1, 'necromancer' => 1, 'guard' => 1, 'escaper' => 1, 'wolf' => 2, 'mad' => 1, 'fox' => 1),
    11 => array('wise_wolf' => 1, 'jammer_mad' => 7, 'voodoo_fox' => 2, 'fairy' => 1),
    12 => array('human' => 5, 'mage' => 1, 'necromancer' => 1, 'guard' => 1, 'wolf' => 2, 'mad' => 1, 'vampire' => 1),
    13 => array('human' => 4, 'mage' => 1, 'necromancer' => 1, 'guard' => 1, 'doll' => 1, 'doll_master' => 1, 'wolf' => 2, 'fanatic_mad' => 1, 'chiroptera' => 1),
    14 => array('human' => 6, 'mage' => 1, 'necromancer' => 1, 'guard' => 1, 'common' => 1, 'wolf' => 2, 'mad' => 1, 'jammer_mad' => 1),
    15 => array('poison' => 3, 'wolf' => 3, 'fanatic_mad' => 1, 'fox' => 1, 'chiroptera' => 6, 'boss_chiroptera' => 1),
    16 => array('human' => 7, 'mage' => 1, 'necromancer' => 1, 'guard' => 1, 'common' => 2, 'wolf' => 3, 'whisper_mad' => 1),
    17 => array('human' => 6, 'mage' => 1, 'necromancer' => 1, 'guard' => 1, 'common' => 2, 'escaper' => 1, 'wolf' => 3, 'mad' => 1, 'fox' => 1),
    18 => array('human' => 7, 'mage' => 1, 'necromancer' => 1, 'guard' => 1, 'common' => 2, 'wolf' => 3, 'mad' => 1, 'fox' => 1, 'vampire' => 1),
    19 => array('human' => 7, 'mage' => 1, 'necromancer' => 1, 'guard' => 1, 'common' => 2, 'poison_cat' => 1, 'wolf' => 4, 'mad' => 1, 'fox' => 1),
    20 => array('human' => 5, 'mage' => 1, 'necromancer' => 1, 'guard' => 1, 'common' => 2, 'doll' => 1, 'doll_master' => 1, 'wolf' => 4, 'fanatic_mad' => 1, 'fox' => 1, 'child_fox' => 1, 'chiroptera' => 1),
    21 => array('poison' => 7, 'chain_poison' => 2, 'poison_wolf' => 4, 'resist_wolf' => 1, 'poison_fox' => 2, 'quiz' => 3, 'poison_chiroptera' => 2),
    22 => array('human' => 8, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'poison_cat' => 1, 'wolf' => 4, 'boss_wolf' => 1, 'fox' => 1, 'child_fox' => 1)
				);

  //決闘村配役データ (実際は InitializeDuel() で設定する)
  var $duel_fix_list = array(); //固定配役
  var $duel_rate_list = array('assassin' => 11, 'wolf' => 4, 'trap_mad' => 5); //配役比率

  //-- 関数 --//
  //決闘村の配役初期化処理
  function InitializeDuel($user_count){
    global $ROOM;

    //-- 霊界自動公開オプションによる配役設定分岐 --//
    if($ROOM->IsOption('not_open_cast')){ //非公開
      //-- 埋毒決闘 --//
      $duel_fix_list = array();
      if($user_count >= 20){
	$duel_fix_list['poison_jealousy'] = 1;
	$duel_fix_list['moon_cupid'] = 1;
      }
      if($user_count >= 25) $duel_fix_list['quiz'] = 1;
      $duel_rate_list = array('poison' => 5, 'chain_poison' => 10,
			      'poison_wolf' => 5, 'triangle_cupid' => 2);
    }
    elseif($ROOM->IsOption('auto_open_cast')){ //自動公開
      //-- 恋色決闘 --//
      $duel_fix_list = array();
      if($user_count >= 15) $duel_fix_list['howl_scanner'] = 1;
      if($user_count >= 20) $duel_fix_list['enchant_mad'] = 1;
      if($user_count >= 25){
	$duel_fix_list['sirius_wolf'] = 1;
	$duel_fix_list['moon_cupid'] = 1;
      }
      if($user_count >= 30) $duel_fix_list['quiz'] = 1;

      $duel_rate_list = array('assassin' => 5, 'wolf' => 3, 'self_cupid' => 1, 'mind_cupid' => 4,
			      'triangle_cupid' => 1);
    }
    else{ //常時公開
      //-- 暗殺決闘 --//
      $duel_fix_list = array();
      $duel_rate_list = array('assassin' => 11, 'wolf' => 4, 'trap_mad' => 5);
    }

    //結果を登録
    $this->duel_fix_list  = $duel_fix_list;
    $this->duel_rate_list = $duel_rate_list;
  }

  //決闘村の配役最終処理
  function FinalizeDuel($user_count, &$role_list){
    global $ROOM;

    if($ROOM->IsOption('not_open_cast')){ //非公開
    }
    elseif($ROOM->IsOption('auto_open_cast')){ //自動公開
      if($role_list['self_cupid'] > 1){
	$role_list['self_cupid']--;
	$role_list['dummy_chiroptera']++;
      }
      if($role_list['mind_cupid'] > 2){
	$role_list['mind_cupid']--;
	$role_list['exchange_angel']++;
      }
      if($role_list['mind_cupid'] > 2){
	$role_list['mind_cupid']--;
	$role_list['sweet_cupid']++;
      }
      if($role_list['wolf'] > 2){
	$role_list['wolf']--;
	$role_list['silver_wolf']++;
      }
    }
    else{ //常時公開
    }
  }

  //村人置換村の処理
  function ReplaceHuman(&$role_list, $count){
    global $ROOM;

    if($ROOM->IsOption('full_mania')){ //神話マニア村
      $role_list['mania'] += $count;
      $role_list['human'] -= $count;
    }
    elseif($ROOM->IsOption('full_chiroptera')){ //蝙蝠村
      $role_list['chiroptera'] += $count;
      $role_list['human'] -= $count;
    }
    elseif($ROOM->IsOption('full_cupid')){ //キューピッド村
      $role_list['cupid'] += $count;
      $role_list['human'] -= $count;
    }
    elseif($ROOM->IsOption('replace_human')){ //村人置換村
      $role_list['escaper'] += $count;
      $role_list['human'] -= $count;
    }
  }
}
