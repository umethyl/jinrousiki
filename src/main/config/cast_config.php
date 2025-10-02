<?php
/*
  変更履歴 from Ver. 1.5.0
  + 変更：なし
*/
//-- 配役設定 --//
class CastConfig extends CastConfigBase{
  //-- 配役テーブル --//
  /* 設定の見方
    [ゲーム参加人数] => array([配役名1] => [配役名1の人数], [配役名2] => [配役名2の人数], ...),
    ゲーム参加人数と配役名の人数の合計が合わない場合はゲーム開始投票時にエラーが返る
  */
  public $role_list = array(
     4 => array('human' =>  1, 'wolf' => 1, 'mage' => 1, 'mad' => 1),
     5 => array('wolf'  =>  1, 'mage' => 2, 'mad'  => 2),
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
    25 => array('human' => 13, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 2),
    26 => array('human' => 14, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 2),
    27 => array('human' => 15, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 1, 'guard' => 1, 'common' => 2, 'fox' => 2),
    28 => array('human' => 14, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 2, 'guard' => 1, 'common' => 3, 'fox' => 2),
    29 => array('human' => 15, 'wolf' => 4, 'mage' => 1, 'necromancer' => 1, 'mad' => 2, 'guard' => 1, 'common' => 3, 'fox' => 2),
    30 => array('human' => 14, 'wolf' => 5, 'mage' => 2, 'necromancer' => 1, 'mad' => 2, 'guard' => 1, 'common' => 3, 'fox' => 2),
    31 => array('human' => 15, 'wolf' => 5, 'mage' => 2, 'necromancer' => 1, 'mad' => 2, 'guard' => 1, 'common' => 3, 'fox' => 2),
    32 => array('human' => 15, 'wolf' => 5, 'mage' => 2, 'necromancer' => 1, 'mad' => 2, 'guard' => 2, 'common' => 3, 'fox' => 2),
    33 => array('human' => 15, 'wolf' => 5, 'mage' => 2, 'necromancer' => 1, 'mad' => 2, 'common' => 3, 'guard' => 2, 'fox' => 3),
    34 => array('human' => 16, 'wolf' => 5, 'mage' => 2, 'necromancer' => 1, 'mad' => 2, 'common' => 3, 'guard' => 2, 'fox' => 3),
    35 => array('human' => 16, 'wolf' => 5, 'mage' => 2, 'necromancer' => 2, 'mad' => 2, 'common' => 3, 'guard' => 2, 'fox' => 3),
    36 => array('human' => 17, 'wolf' => 5, 'mage' => 2, 'necromancer' => 2, 'mad' => 2, 'common' => 3, 'guard' => 2, 'fox' => 3),
    37 => array('human' => 17, 'wolf' => 6, 'mage' => 2, 'necromancer' => 2, 'mad' => 2, 'common' => 3, 'guard' => 2, 'fox' => 3),
    38 => array('human' => 18, 'wolf' => 6, 'mage' => 2, 'necromancer' => 2, 'mad' => 2, 'common' => 3, 'guard' => 2, 'fox' => 3),
    39 => array('human' => 19, 'wolf' => 6, 'mage' => 2, 'necromancer' => 2, 'mad' => 2, 'common' => 3, 'guard' => 2, 'fox' => 3),
    40 => array('human' => 19, 'wolf' => 6, 'mage' => 2, 'necromancer' => 2, 'mad' => 3, 'common' => 3, 'guard' => 2, 'fox' => 3),
    41 => array('human' => 20, 'wolf' => 6, 'mage' => 2, 'necromancer' => 2, 'mad' => 3, 'common' => 3, 'guard' => 2, 'fox' => 3),
    42 => array('human' => 20, 'wolf' => 6, 'mage' => 2, 'necromancer' => 2, 'mad' => 3, 'common' => 3, 'guard' => 2, 'fox' => 4),
    43 => array('human' => 20, 'wolf' => 6, 'mage' => 2, 'necromancer' => 2, 'mad' => 3, 'common' => 4, 'guard' => 2, 'fox' => 4),
    44 => array('human' => 20, 'wolf' => 7, 'mage' => 2, 'necromancer' => 2, 'mad' => 3, 'common' => 4, 'guard' => 2, 'fox' => 4),
    45 => array('human' => 21, 'wolf' => 7, 'mage' => 2, 'necromancer' => 2, 'mad' => 3, 'common' => 4, 'guard' => 2, 'fox' => 4),
    46 => array('human' => 22, 'wolf' => 7, 'mage' => 2, 'necromancer' => 2, 'mad' => 3, 'common' => 4, 'guard' => 2, 'fox' => 4),
    47 => array('human' => 23, 'wolf' => 7, 'mage' => 2, 'necromancer' => 2, 'mad' => 3, 'common' => 4, 'guard' => 2, 'fox' => 4),
    48 => array('human' => 24, 'wolf' => 7, 'mage' => 2, 'necromancer' => 2, 'mad' => 3, 'common' => 4, 'guard' => 2, 'fox' => 4),
    49 => array('human' => 25, 'wolf' => 7, 'mage' => 2, 'necromancer' => 2, 'mad' => 3, 'common' => 4, 'guard' => 2, 'fox' => 4),
    50 => array('human' => 26, 'wolf' => 7, 'mage' => 2, 'necromancer' => 2, 'mad' => 3, 'common' => 4, 'guard' => 2, 'fox' => 4)
                         );
  //-- 役職出現人数 --//
  //各役職の出現に必要な人数を設定する
  public $poison         = 20; //埋毒者 [村人2 → 埋毒者1、人狼1]
  public $assassin       = 22; //暗殺者 [村人2 → 暗殺者1、人狼1]
  public $wolf           = 20; //人狼追加 [村人1 → 人狼1]
  public $boss_wolf      = 18; //白狼 [人狼1 → 白狼]
  public $poison_wolf    = 20; //毒狼 (+ 薬師) [人狼1 → 毒狼1、村人1 → 薬師1]
  public $possessed_wolf =  8; //憑狼 [人狼1 → 憑狼1]
  public $sirius_wolf    =  8; //天狼 [人狼1 → 天狼1]
  public $fox            =  8; //妖狐追加 [村人1 → 妖狐1]
  public $child_fox      =  8; //子狐 [妖狐1 → 子狐1]
  public $cupid          = 16; //キューピッド [村人1 → キューピッド1]
  public $medium         = 20; //巫女 (+ 女神) [村人2 → 巫女1、女神1]
  public $mania          = 16; //神話マニア [村人1 → 神話マニア1]
  public $decide         = 16; //決定者 [兼任]
  public $authority      = 16; //権力者 [兼任]

  //希望制で役職希望が通る確率 (%) (身代わり君がいる場合は 100% にしても保証されない)
  public $wish_role_rate = 100;

  //身代わり君がならない役職グループのリスト (人狼・妖狐は常時対象外なので設定不要)
  public $disable_dummy_boy_role_list = array('poison');

  //-- 役職置換モード --//
  //オプション名 => 置換先役職
  public $replace_role_list = array(
    'replace_human' => 'escaper',
    'change_common' => 'leader_common',
    'change_mad'    => 'jammer_mad',
    'change_cupid'  => 'exchange_angel');

  //-- 闇鍋モード --//
  //-- 固定枠 --//
  //闇鍋
  public $chaos_fix_role_list = array('mage' => 1, 'wolf' => 1);

  //真・闇鍋
  public $chaosfull_fix_role_list = array('mage' => 1, 'wolf' => 1);

  //超・闇鍋
  public $chaos_hyper_fix_role_list = array('mage' => 1, 'wolf' => 1);

  //裏・闇鍋
  public $chaos_verso_fix_role_list = array();

  //-- 配役テーブル --//
  //人狼の最小出現枠 (役職名 => 出現比)
  //闇鍋
  public $chaos_wolf_list = array(
    'wolf'        => 60,
    'boss_wolf'   =>  5,
    'poison_wolf' => 10,
    'tongue_wolf' =>  5,
    'silver_wolf' => 20);

  //真・闇鍋
  public $chaosfull_wolf_list = array(
    'wolf'        => 60,
    'boss_wolf'   =>  5,
    'cursed_wolf' =>  5,
    'poison_wolf' =>  5,
    'resist_wolf' =>  5,
    'tongue_wolf' =>  5,
    'cute_wolf'   => 10,
    'silver_wolf' =>  5);

  //超・闇鍋
  public $chaos_hyper_wolf_list = array(
    'wolf'           => 20,
    'boss_wolf'      =>  2,
    'mist_wolf'      =>  2,
    'gold_wolf'      =>  3,
    'phantom_wolf'   =>  2,
    'cursed_wolf'    =>  2,
    'quiet_wolf'     =>  2,
    'wise_wolf'      =>  3,
    'poison_wolf'    =>  3,
    'resist_wolf'    =>  2,
    'revive_wolf'    =>  2,
    'trap_wolf'      =>  2,
    'blue_wolf'      =>  3,
    'emerald_wolf'   =>  3,
    'doom_wolf'      =>  2,
    'fire_wolf'      =>  2,
    'sex_wolf'       =>  3,
    'sharp_wolf'     =>  2,
    'hungry_wolf'    =>  3,
    'tongue_wolf'    =>  2,
    'possessed_wolf' =>  2,
    'sirius_wolf'    =>  2,
    'elder_wolf'     =>  3,
    'cute_wolf'      => 10,
    'scarlet_wolf'   =>  3,
    'silver_wolf'    => 10,
    'emperor_wolf'   =>  5);

  //妖狐の最小出現枠 (役職名 => 出現比)
  //闇鍋
  public $chaos_fox_list = array(
    'fox'       => 90,
    'child_fox' => 10);

  //真・闇鍋
  public $chaosfull_fox_list = array(
    'fox'        => 80,
    'white_fox'  =>  3,
    'poison_fox' =>  3,
    'voodoo_fox' =>  3,
    'cursed_fox' =>  3,
    'silver_fox' =>  3,
    'child_fox'  =>  5);

  //超・闇鍋
  public $chaos_hyper_fox_list = array(
    'fox'           => 30,
    'white_fox'     =>  2,
    'black_fox'     =>  2,
    'mist_fox'      =>  2,
    'gold_fox'      =>  3,
    'phantom_fox'   =>  2,
    'poison_fox'    =>  3,
    'blue_fox'      =>  2,
    'spell_fox'     =>  2,
    'sacrifice_fox' =>  2,
    'emerald_fox'   =>  2,
    'voodoo_fox'    =>  2,
    'revive_fox'    =>  2,
    'possessed_fox' =>  2,
    'doom_fox'      =>  2,
    'trap_fox'      =>  2,
    'cursed_fox'    =>  2,
    'elder_fox'     =>  3,
    'cute_fox'      =>  5,
    'scarlet_fox'   =>  3,
    'silver_fox'    =>  3,
    'immolate_fox'  =>  2,
    'child_fox'     =>  6,
    'sex_fox'       =>  2,
    'stargazer_fox' =>  2,
    'jammer_fox'    =>  2,
    'monk_fox'      =>  2,
    'miasma_fox'    =>  2,
    'howl_fox'      =>  2,
    'critical_fox'  =>  2);

  //ランダム配役テーブル (役職名 => 出現比)
  //闇鍋
  public $chaos_random_role_list = array(
    'human'           => 88,
    'mage'            => 50,
    'soul_mage'       =>  5,
    'psycho_mage'     => 10,
    'necromancer'     => 60,
    'medium'          => 30,
    'guard'           => 70,
    'poison_guard'    =>  5,
    'reporter'        => 15,
    'common'          => 75,
    'poison'          => 40,
    'incubate_poison' => 10,
    'pharmacist'      => 20,
    'assassin'        => 20,
    'doll'            => 20,
    'doll_master'     => 10,
    'escaper'         => 30,
    'wolf'            => 80,
    'boss_wolf'       => 10,
    'poison_wolf'     => 40,
    'tongue_wolf'     => 20,
    'silver_wolf'     => 30,
    'mad'             => 60,
    'fanatic_mad'     => 20,
    'whisper_mad'     => 10,
    'fox'             => 50,
    'child_fox'       => 20,
    'cupid'           => 30,
    'self_cupid'      => 10,
    'quiz'            =>  2,
    'chiroptera'      => 50,
    'mania'           => 10);

  //真・闇鍋
  public $chaosfull_random_role_list = array(
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
  public $chaos_hyper_random_role_list = array(
    'human'                =>  1,
    'saint'                =>  1,
    'executor'             =>  1,
    'elder'                =>  2,
    'scripter'             =>  1,
    'suspect'              =>  2,
    'unconscious'          =>  2,
    'mage'                 => 10,
    'puppet_mage'          =>  6,
    'soul_mage'            =>  3,
    'psycho_mage'          =>  5,
    'sex_mage'             =>  5,
    'stargazer_mage'       =>  5,
    'voodoo_killer'        =>  9,
    'cute_mage'            =>  5,
    'dummy_mage'           =>  7,
    'necromancer'          => 10,
    'soul_necromancer'     =>  3,
    'psycho_necromancer'   =>  6,
    'embalm_necromancer'   =>  6,
    'emissary_necromancer' =>  3,
    'attempt_necromancer'  =>  6,
    'yama_necromancer'     =>  9,
    'dummy_necromancer'    =>  7,
    'medium'               =>  5,
    'bacchus_medium'       =>  5,
    'seal_medium'          =>  4,
    'revive_medium'        =>  3,
    'eclipse_medium'       =>  3,
    'priest'               =>  5,
    'bishop_priest'        =>  2,
    'dowser_priest'        =>  2,
    'weather_priest'       =>  2,
    'high_priest'          =>  2,
    'crisis_priest'        =>  3,
    'widow_priest'         =>  2,
    'holy_priest'          =>  2,
    'revive_priest'        => 10,
    'border_priest'        =>  2,
    'dummy_priest'         =>  3,
    'guard'                => 12,
    'hunter_guard'         =>  9,
    'blind_guard'          =>  5,
    'gatekeeper_guard'     =>  5,
    'reflect_guard'        =>  5,
    'poison_guard'         =>  3,
    'fend_guard'           =>  5,
    'reporter'             => 10,
    'anti_voodoo'          => 15,
    'elder_guard'          =>  7,
    'dummy_guard'          =>  9,
    'common'               => 25,
    'leader_common'        =>  4,
    'detective_common'     =>  4,
    'trap_common'          =>  5,
    'sacrifice_common'     =>  5,
    'ghost_common'         =>  3,
    'spell_common'         =>  4,
    'critical_common'      =>  4,
    'hermit_common'        =>  6,
    'dummy_common'         => 15,
    'poison'               => 12,
    'strong_poison'        =>  3,
    'incubate_poison'      =>  8,
    'guide_poison'         =>  6,
    'snipe_poison'         =>  6,
    'chain_poison'         =>  6,
    'dummy_poison'         =>  9,
    'poison_cat'           =>  4,
    'revive_cat'           =>  3,
    'sacrifice_cat'        =>  3,
    'missfire_cat'         =>  2,
    'eclipse_cat'          =>  3,
    'pharmacist'           =>  6,
    'cure_pharmacist'      =>  4,
    'revive_pharmacist'    =>  3,
    'alchemy_pharmacist'   =>  3,
    'centaurus_pharmacist' =>  4,
    'assassin'             =>  4,
    'doom_assassin'        =>  2,
    'select_assassin'      =>  2,
    'reverse_assassin'     =>  2,
    'soul_assassin'        =>  2,
    'eclipse_assassin'     =>  3,
    'mind_scanner'         =>  4,
    'evoke_scanner'        =>  3,
    'presage_scanner'      =>  3,
    'clairvoyance_scanner' =>  2,
    'whisper_scanner'      =>  2,
    'howl_scanner'         =>  2,
    'telepath_scanner'     =>  2,
    'dummy_scanner'        =>  2,
    'jealousy'             =>  2,
    'divorce_jealousy'     =>  2,
    'priest_jealousy'      =>  2,
    'poison_jealousy'      =>  1,
    'miasma_jealousy'      =>  1,
    'critical_jealousy'    =>  2,
    'brownie'              =>  2,
    'thunder_brownie'      =>  1,
    'echo_brownie'         =>  1,
    'revive_brownie'       =>  1,
    'harvest_brownie'      =>  1,
    'maple_brownie'        =>  1,
    'cursed_brownie'       =>  1,
    'sun_brownie'          =>  1,
    'history_brownie'      =>  1,
    'wizard'               =>  2,
    'soul_wizard'          =>  1,
    'awake_wizard'         =>  2,
    'mimic_wizard'         =>  2,
    'spiritism_wizard'     =>  2,
    'philosophy_wizard'    =>  2,
    'barrier_wizard'       =>  1,
    'astray_wizard'        =>  2,
    'pierrot_wizard'       =>  1,
    'doll'                 =>  5,
    'friend_doll'          =>  3,
    'phantom_doll'         =>  2,
    'poison_doll'          =>  2,
    'doom_doll'            =>  2,
    'revive_doll'          =>  2,
    'scarlet_doll'         =>  2,
    'silver_doll'          =>  2,
    'doll_master'          => 10,
    'escaper'              =>  2,
    'psycho_escaper'       =>  2,
    'incubus_escaper'      =>  2,
    'succubus_escaper'     =>  2,
    'doom_escaper'         =>  1,
    'divine_escaper'       =>  1,
    'wolf'                 => 15,
    'boss_wolf'            =>  5,
    'mist_wolf'            =>  5,
    'gold_wolf'            =>  5,
    'phantom_wolf'         =>  5,
    'cursed_wolf'          =>  5,
    'quiet_wolf'           =>  5,
    'wise_wolf'            => 10,
    'poison_wolf'          => 15,
    'resist_wolf'          => 10,
    'revive_wolf'          =>  5,
    'trap_wolf'            =>  5,
    'blue_wolf'            =>  5,
    'emerald_wolf'         =>  5,
    'doom_wolf'            =>  5,
    'fire_wolf'            =>  5,
    'sex_wolf'             =>  5,
    'sharp_wolf'           =>  5,
    'hungry_wolf'          =>  5,
    'tongue_wolf'          =>  5,
    'possessed_wolf'       =>  5,
    'sirius_wolf'          =>  5,
    'elder_wolf'           =>  5,
    'cute_wolf'            => 10,
    'scarlet_wolf'         => 10,
    'silver_wolf'          => 10,
    'emperor_wolf'         =>  5,
    'mad'                  => 10,
    'fanatic_mad'          =>  5,
    'whisper_mad'          =>  3,
    'swindle_mad'          =>  4,
    'jammer_mad'           =>  5,
    'voodoo_mad'           =>  4,
    'enchant_mad'          =>  4,
    'dream_eater_mad'      =>  5,
    'possessed_mad'        =>  4,
    'trap_mad'             =>  4,
    'snow_trap_mad'        =>  4,
    'corpse_courier_mad'   =>  4,
    'amaze_mad'            =>  3,
    'agitate_mad'          =>  3,
    'miasma_mad'           =>  3,
    'critical_mad'         =>  3,
    'follow_mad'           =>  4,
    'therian_mad'          =>  4,
    'revive_mad'           =>  4,
    'immolate_mad'         =>  5,
    'fox'                  =>  5,
    'white_fox'            =>  2,
    'black_fox'            =>  2,
    'mist_fox'             =>  2,
    'gold_fox'             =>  2,
    'phantom_fox'          =>  2,
    'poison_fox'           =>  2,
    'blue_fox'             =>  2,
    'spell_fox'            =>  2,
    'sacrifice_fox'        =>  2,
    'emerald_fox'          =>  2,
    'voodoo_fox'           =>  2,
    'revive_fox'           =>  2,
    'possessed_fox'        =>  2,
    'doom_fox'             =>  2,
    'trap_fox'             =>  2,
    'cursed_fox'           =>  2,
    'elder_fox'            =>  2,
    'cute_fox'             =>  3,
    'scarlet_fox'          =>  3,
    'silver_fox'           =>  2,
    'immolate_fox'         =>  3,
    'child_fox'            =>  3,
    'sex_fox'              =>  2,
    'stargazer_fox'        =>  2,
    'jammer_fox'           =>  1,
    'monk_fox'             =>  1,
    'miasma_fox'           =>  2,
    'howl_fox'             =>  2,
    'critical_fox'         =>  2,
    'cupid'                =>  3,
    'self_cupid'           =>  3,
    'moon_cupid'           =>  2,
    'mind_cupid'           =>  2,
    'sweet_cupid'          =>  2,
    'minstrel_cupid'       =>  1,
    'triangle_cupid'       =>  2,
    'revive_cupid'         =>  1,
    'snow_cupid'           =>  2,
    'angel'                =>  3,
    'rose_angel'           =>  2,
    'lily_angel'           =>  2,
    'exchange_angel'       =>  2,
    'ark_angel'            =>  2,
    'sacrifice_angel'      =>  2,
    'scarlet_angel'        =>  2,
    'cursed_angel'         =>  2,
    'quiz'                 =>  2,
    'vampire'              =>  4,
    'incubus_vampire'      =>  2,
    'succubus_vampire'     =>  2,
    'doom_vampire'         =>  2,
    'sacrifice_vampire'    =>  2,
    'soul_vampire'         =>  1,
    'scarlet_vampire'      =>  2,
    'chiroptera'           =>  4,
    'poison_chiroptera'    =>  3,
    'cursed_chiroptera'    =>  2,
    'boss_chiroptera'      =>  2,
    'elder_chiroptera'     =>  2,
    'cute_chiroptera'      =>  3,
    'scarlet_chiroptera'   =>  3,
    'dummy_chiroptera'     =>  2,
    'fairy'                =>  1,
    'spring_fairy'         =>  1,
    'summer_fairy'         =>  1,
    'autumn_fairy'         =>  1,
    'winter_fairy'         =>  1,
    'flower_fairy'         =>  1,
    'star_fairy'           =>  1,
    'sun_fairy'            =>  1,
    'moon_fairy'           =>  1,
    'grass_fairy'          =>  1,
    'light_fairy'          =>  1,
    'dark_fairy'           =>  1,
    'shadow_fairy'         =>  1,
    'greater_fairy'        =>  1,
    'mirror_fairy'         =>  1,
    'sweet_fairy'          =>  1,
    'ice_fairy'            =>  1,
    'ogre'                 =>  1,
    'orange_ogre'          =>  1,
    'indigo_ogre'          =>  1,
    'poison_ogre'          =>  1,
    'west_ogre'            =>  1,
    'east_ogre'            =>  1,
    'north_ogre'           =>  1,
    'south_ogre'           =>  1,
    'incubus_ogre'         =>  1,
    'wise_ogre'            =>  1,
    'power_ogre'           =>  1,
    'revive_ogre'          =>  1,
    'sacrifice_ogre'       =>  1,
    'yaksa'                =>  1,
    'betray_yaksa'         =>  1,
    'cursed_yaksa'         =>  1,
    'succubus_yaksa'       =>  1,
    'hariti_yaksa'         =>  1,
    'power_yaksa'          =>  1,
    'dowser_yaksa'         =>  1,
    'duelist'              =>  1,
    'valkyrja_duelist'     =>  1,
    'critical_duelist'     =>  1,
    'triangle_duelist'     =>  1,
    'doom_duelist'         =>  1,
    'cowboy_duelist'       =>  1,
    'avenger'              =>  1,
    'poison_avenger'       =>  1,
    'cursed_avenger'       =>  1,
    'critical_avenger'     =>  1,
    'revive_avenger'       =>  1,
    'cute_avenger'         =>  1,
    'patron'               =>  3,
    'soul_patron'          =>  1,
    'sacrifice_patron'     =>  1,
    'shepherd_patron'      =>  1,
    'critical_patron'      =>  2,
    'mania'                =>  3,
    'trick_mania'          =>  2,
    'basic_mania'          =>  2,
    'soul_mania'           =>  1,
    'dummy_mania'          =>  1,
    'unknown_mania'        =>  1,
    'wirepuller_mania'     =>  1,
    'fire_mania'           =>  1,
    'sacrifice_mania'      =>  1,
    'resurrect_mania'      =>  1,
    'revive_mania'         =>  1);

  //裏・闇鍋
  public $chaos_verso_random_role_list = array(
    'human'       => 14,
    'mage'        => 10,
    'necromancer' => 10,
    'guard'       =>  5,
    'common'      => 10,
    'poison'      =>  5,
    'assassin'    =>  5,
    'wolf'        => 20,
    'mad'         => 10,
    'fanatic_mad' =>  5,
    'fox'         =>  5,
    'quiz'        =>  1);

  //村人から振り返る役職 => 出現比
  //闇鍋
  public $chaos_replace_human_role_list = array('mania' => 1);

  //真・闇鍋
  public $chaosfull_replace_human_role_list = array('mania' => 7, 'unknown_mania' => 3);

  //超・闇鍋
  public $chaos_hyper_replace_human_role_list = array(
    'mania'            => 20,
    'trick_mania'      => 12,
    'basic_mania'      => 12,
    'soul_mania'       =>  9,
    'dummy_mania'      =>  7,
    'unknown_mania'    => 10,
    'wirepuller_mania' =>  6,
    'fire_mania'       =>  6,
    'sacrifice_mania'  =>  6,
    'resurrect_mania'  =>  6,
    'revive_mania'     =>  6);

  //-- 出現補正値 --//
  public $chaos_min_wolf_rate  = 10; //人狼の最小出現比 (総人口 / N)
  public $chaos_min_fox_rate   = 15; //妖狐の最小出現比 (総人口 / N)
  public $chaos_max_human_rate = 10; //村人の最大出現比 (総人口 / N)

  //役職グループの最大出現比 (グループ => 総人口 / N)
  public $chaos_role_group_rate_list = array(
    'mage'        =>  6.2,
    'necromancer' =>  6.7,
    'medium'      =>  8,
    'priest'      =>  6.7,
    'guard'       =>  7.1,
    'common'      =>  6.7,
    'poison'      =>  7.1,
    'cat'         => 10,
    'pharmacist'  =>  6.7,
    'assassin'    =>  7.1,
    'scanner'     =>  6.7,
    'jealousy'    =>  8,
    'wizard'      =>  6.7,
    'doll'        =>  6.7,
    'escaper'     =>  7.1,
    'wolf'        =>  4.8,
    'mad'         =>  7.1,
    'fox'         => 10,
    'child_fox'   => 12,
    'cupid'       => 10,
    'angel'       => 12,
    'quiz'        =>  6.7,
    'vampire'     =>  6.7,
    'chiroptera'  =>  8,
    'fairy'       =>  8,
    'ogre'        =>  8,
    'yaksa'       =>  8,
    'duelist'     => 14.2,
    'avenger'     => 14.2,
    'patron'      => 12);

  //-- 固定配役追加モード --//
  /*
    fix    : 固定枠
    random : ランダム枠 (各配列の中身は役職 => 出現比)
    count  : ランダム出現数 (ランダム枠毎の出現数)

    例)
    doll_master が +1, [doll:poison_doll = 2:1] の割合でランダムに +1,
    [scarlet_doll:silver_doll = 5:1] の割合でランダムに +2
    'a' => array('fix'    => array('doll_master' => 1),
		 'random' => array(array('doll'  => 2, 'poison_doll' => 1),
				   array('scarlet_doll' => 5, 'silver_doll' => 1)),
		 'count'  => array(1, 2)),
  */
  public $topping_list = array(
    'a' => array('fix' => array('doll_master' => 1),
		 'random' => array(
                    array('doll'         => 30,
			  'friend_doll'  =>  5,
			  'phantom_doll' => 10,
			  'poison_doll'  => 15,
			  'doom_doll'    => 15,
			  'revive_doll'  => 10,
			  'scarlet_doll' => 10,
			  'silver_doll'  =>  5),
                    array('puppet_mage'        => 15,
			  'scarlet_doll'       =>  5,
			  'scarlet_wolf'       => 30,
			  'scarlet_fox'        => 15,
			  'scarlet_angel'      => 15,
			  'scarlet_vampire'    => 10,
			  'scarlet_chiroptera' => 10)),
		 'count'  => array(1, 1)),
    'b' => array('fix' => array('quiz' => 1, 'poison_ogre' => 1)),
    'c' => array('random' => array(
                   array('vampire'           => 3,
			 'incubus_vampire'   => 1,
			 'succubus_vampire'  => 1,
			 'doom_vampire'      => 1,
			 'sacrifice_vampire' => 1,
			 'soul_vampire'      => 1,
			 'scarlet_vampire'   => 2)),
		 'count' => array(1)),
    'd' => array('fix' => array('resist_wolf' => 1),
		 'random' => array(
                    array('poison_cat'    => 3,
			  'revive_cat'    => 2,
			  'sacrifice_cat' => 2,
			  'missfire_cat'  => 1,
			  'eclipse_cat'   => 2)),
		 'count'  => array(1)),
    'e' => array('fix' => array('anti_voodoo' => 1, 'possessed_wolf' => 1)),
    'f' => array('random' => array(
                   array('ogre'           => 10,
			 'orange_ogre'    =>  5,
			 'indigo_ogre'    =>  5,
			 'poison_ogre'    =>  5,
			 'west_ogre'      =>  3,
			 'east_ogre'      =>  3,
			 'north_ogre'     =>  3,
			 'south_ogre'     =>  3,
			 'incubus_ogre'   =>  5,
			 'wise_ogre'      =>  5,
			 'power_ogre'     =>  5,
			 'revive_ogre'    =>  5,
			 'sacrifice_ogre' =>  3,
			 'yaksa'          => 10,
			 'betray_yaksa'   =>  5,
			 'cursed_yaksa'   =>  5,
			 'succubus_yaksa' =>  5,
			 'hariti_yaksa'   =>  5,
			 'power_yaksa'    =>  5,
			 'dowser_yaksa'   =>  5)),
		 'count' => array(2)),
    'g' => array('random' => array(
		   array('mad'                => 1,
			 'fanatic_mad'        => 1,
			 'whisper_mad'        => 1,
			 'swindle_mad'        => 1,
			 'jammer_mad'         => 1,
			 'voodoo_mad'         => 1,
			 'enchant_mad'        => 1,
			 'dream_eater_mad'    => 1,
			 'possessed_mad'      => 1,
			 'trap_mad'           => 1,
			 'snow_trap_mad'      => 1,
			 'corpse_courier_mad' => 1,
			 'amaze_mad'          => 1,
			 'agitate_mad'        => 1,
			 'miasma_mad'         => 1,
			 'critical_mad'       => 1,
			 'follow_mad'         => 1,
			 'therian_mad'        => 1,
			 'revive_mad'         => 1,
			 'immolate_mad'       => 1),
                   array('suspect'           => 1,
			 'unconscious'       => 1,
			 'dummy_mage'        => 1,
			 'dummy_necromancer' => 1,
			 'dummy_priest'      => 1,
			 'dummy_guard'       => 1,
			 'dummy_common'      => 1,
			 'dummy_poison'      => 1,
			 'dummy_scanner'     => 1,
			 'dummy_chiroptera'  => 1,
			 'dummy_mania'       => 1),
		   array('psycho_mage'        => 10,
			 'psycho_necromancer' =>  5,
			 'psycho_escaper'     => 20,
			 'dream_eater_mad'    => 10,
			 'revive_ogre'        =>  5)),
		 'count' => array(1, 1, 1)),
    'h' => array('fix' => array('human' => 2)),
    'i' => array('random' => array(
		   array('jealousy'          => 30,
			 'divorce_jealousy'  => 20,
			 'priest_jealousy'   => 15,
			 'poison_jealousy'   => 10,
			 'miasma_jealousy'   =>  5,
			 'critical_jealousy' => 20),
                   array('cupid'           => 10,
			 'self_cupid'      =>  8,
			 'moon_cupid'      =>  5,
			 'mind_cupid'      =>  3,
			 'sweet_cupid'     =>  5,
			 'minstrel_cupid'  =>  3,
			 'triangle_cupid'  =>  5,
			 'revive_cupid'    =>  3,
			 'snow_cupid'      =>  8,
			 'angel'           =>  8,
			 'rose_angel'      =>  8,
			 'lily_angel'      =>  8,
			 'exchange_angel'  =>  5,
			 'ark_angel'       =>  5,
			 'sacrifice_angel' =>  5,
			 'scarlet_angel'   =>  5,
			 'cursed_angel'    =>  6)),
		 'count' => array(1, 2)),
    'j' => array('random' => array(
		   array('duelist'          => 15,
			 'valkyrja_duelist' => 10,
			 'critical_duelist' =>  5,
			 'triangle_duelist' =>  5,
			 'doom_duelist'     =>  5,
			 'cowboy_duelist'   => 10,
			 'avenger'          =>  5,
			 'poison_avenger'   =>  3,
			 'cursed_avenger'   =>  3,
			 'critical_avenger' =>  3,
			 'revive_avenger'   =>  3,
			 'cute_avenger'     =>  3,
			 'patron'           => 10,
			 'soul_patron'      =>  4,
			 'sacrifice_patron' =>  4,
			 'shepherd_patron'  =>  6,
			 'critical_patron'  =>  6)),
		 'count' => array(1)),
    'k' => array('random' => array(
		   array('executor'             => 8,
			 'soul_mage'            => 4,
			 'soul_necromancer'     => 6,
			 'revive_medium'        => 6,
			 'high_priest'          => 6,
			 'poison_guard'         => 4,
			 'ghost_common'         => 4,
			 'strong_poison'        => 6,
			 'revive_cat'           => 6,
			 'alchemy_pharmacist'   => 6,
			 'soul_assassin'        => 4,
			 'clairvoyance_scanner' => 6,
			 'miasma_jealousy'      => 6,
			 'history_brownie'      => 6,
			 'soul_wizard'          => 6,
			 'doll_master'          => 8,
			 'divine_escaper'       => 8),
                   array('boss_wolf'      => 2,
			 'resist_wolf'    => 2,
			 'tongue_wolf'    => 2,
			 'possessed_wolf' => 1,
			 'sirius_wolf'    => 1,
			 'whisper_mad'    => 3),
		   array('cursed_fox'       => 10,
			 'jammer_fox'       =>  5,
			 'minstrel_cupid'   =>  5,
			 'sacrifice_angel'  => 10,
			 'quiz'             =>  5,
			 'soul_vampire'     => 15,
			 'boss_chiroptera'  => 10,
			 'ice_fairy'        =>  5,
			 'sacrifice_ogre'   =>  5,
			 'dowser_yaksa'     => 10,
			 'critical_duelist' =>  4,
			 'revive_avenger'   =>  3,
			 'sacrifice_patron' =>  3,
			 'soul_mania'       =>  5,
			 'sacrifice_mania'  =>  5)),
		 'count' => array(1, 1, 1)),
    'l' => array('fix' => array('ghost_common' => 1, 'boss_wolf' => 1,
				'silver_wolf' => 1, 'howl_fox' => 1))
			    );

  //-- 出現率変動モード --//
  /* 役職 => 倍率 (0 なら出現しなくなる) */
  public $boost_rate_list = array(
    'a' => array('swindle_mad' =>  7),
    'b' => array('elder'             => 0,
		 'scripter'          => 0,
		 'elder_guard'       => 0,
		 'critical_common'   => 0,
		 'critical_jealousy' => 0,
		 'brownie'           => 0,
		 'harvest_brownie'   => 0,
		 'maple_brownie'     => 0,
		 'philosophy_wizard' => 0,
		 'divine_escaper'    => 0,
		 'elder_wolf'        => 0,
		 'elder_fox'         => 0,
		 'elder_chiroptera'  => 0,
		 'critical_mad'      => 0,
		 'critical_fox'      => 0,
		 'poison_ogre'       => 0,
		 'critical_duelist'  => 0,
		 'cowboy_duelist'    => 0,
		 'critical_avenger'  => 0,
		 'critical_patron'   => 0,
		 'wirepuller_mania'  => 0),
    'c' => array('human'         => 0,
		 'mage'          => 0,
		 'necromancer'   => 0,
		 'medium'        => 0,
		 'priest'        => 0,
		 'guard'         => 0,
		 'common'        => 0,
		 'poison'        => 0,
		 'poison_cat'    => 0,
		 'pharmacist'    => 0,
		 'assassin'      => 0,
		 'mind_scanner'  => 0,
		 'jealousy'      => 0,
		 'brownie'       => 0,
		 'wizard'        => 0,
		 'doll'          => 0,
		 'escaper'       => 0,
		 'wolf'          => 0,
		 'mad'           => 0,
		 'fox'           => 0,
		 'child_fox'     => 0,
		 'cupid'         => 0,
		 'angel'         => 0,
		 'quiz'          => 0,
		 'vampire'       => 0,
		 'chiroptera'    => 0,
		 'fairy'         => 0,
		 'ogre'          => 0,
		 'yaksa'         => 0,
		 'duelist'       => 0,
		 'avenger'       => 0,
		 'patron'        => 0,
		 'mania'         => 0,
		 'unknown_mania' => 0),
    'd' => array('revive_medium' => 0,
		 'poison_cat'    => 0,
		 'revive_cat'    => 0,
		 'sacrifice_cat' => 0,
		 'missfire_cat'  => 0,
		 'eclipse_cat'   => 0,
		 'revive_fox'    => 0,
		 'revive_mania'  => 0),
    'e' => array('possessed_wolf' => 0,
		 'possessed_mad'  => 0,
		 'possessed_fox'  => 0,
		 'exchange_angel' => 0),
    'f' => array('chiroptera'         =>  0,
		 'poison_chiroptera'  =>  0,
		 'cursed_chiroptera'  =>  0,
		 'boss_chiroptera'    =>  0,
		 'elder_chiroptera'   =>  0,
		 'cute_chiroptera'    =>  0,
		 'scarlet_chiroptera' =>  0,
		 'dummy_chiroptera'   =>  0,
		 'fairy'              =>  0,
		 'spring_fairy'       =>  0,
		 'summer_fairy'       =>  0,
		 'autumn_fairy'       =>  0,
		 'winter_fairy'       =>  0,
		 'flower_fairy'       =>  0,
		 'star_fairy'         =>  0,
		 'sun_fairy'          =>  0,
		 'moon_fairy'         =>  0,
		 'grass_fairy'        =>  0,
		 'light_fairy'        =>  0,
		 'dark_fairy'         =>  0,
		 'shadow_fairy'       =>  0,
		 'greater_fairy'      =>  0,
		 'mirror_fairy'       =>  0,
		 'sweet_fairy'        =>  0,
		 'ice_fairy'          =>  0,
		 'ogre'               =>  0,
		 'orange_ogre'        =>  0,
		 'indigo_ogre'        =>  0,
		 'poison_ogre'        =>  0,
		 'west_ogre'          =>  0,
		 'east_ogre'          =>  0,
		 'north_ogre'         =>  0,
		 'south_ogre'         =>  0,
		 'incubus_ogre'       =>  0,
		 'wise_ogre'          =>  0,
		 'power_ogre'         =>  0,
		 'revive_ogre'        =>  0,
		 'sacrifice_ogre'     =>  0,
		 'yaksa'              =>  0,
		 'betray_yaksa'       =>  0,
		 'cursed_yaksa'       =>  0,
		 'succubus_yaksa'     =>  0,
		 'hariti_yaksa'       =>  0,
		 'power_yaksa'        =>  0,
		 'dowser_yaksa'       =>  0,
		 'duelist'            =>  0,
		 'valkyrja_duelist'   =>  0,
		 'doom_duelist'       =>  0,
		 'critical_duelist'   =>  0,
		 'triangle_duelist'   =>  0,
		 'cowboy_duelist'     =>  0,
		 'avenger'            =>  0,
		 'poison_avenger'     =>  0,
		 'cursed_avenger'     =>  0,
		 'critical_avenger'   =>  0,
		 'revive_avenger'     =>  0,
		 'cute_avenger'       =>  0,
		 'patron'             =>  0,
		 'soul_patron'        =>  0,
		 'sacrifice_patron'   =>  0,
		 'shepherd_patron'    =>  0,
		 'critical_patron'    =>  0)
			       );

  //サブ役職制限：EASYモード
  public $chaos_sub_role_limit_easy_list = array(
    'decide', 'plague', 'counter_decide', 'dropout', 'good_luck', 'bad_luck', 'authority',
    'reduce_voter', 'upper_voter', 'downer_voter', 'critical_voter', 'random_voter', 'rebel',
    'watcher');

  //サブ役職制限：NORMALモード
  public $chaos_sub_role_limit_normal_list = array(
    'decide', 'plague', 'counter_decide', 'dropout', 'good_luck', 'bad_luck', 'authority',
    'reduce_voter', 'upper_voter', 'downer_voter', 'critical_voter', 'random_voter', 'rebel',
    'watcher', 'upper_luck', 'downer_luck', 'star', 'disfavor', 'critical_luck', 'random_luck',
    'wisp', 'black_wisp', 'spell_wisp', 'foughten_wisp', 'gold_wisp');

  //サブ役職制限：HARDモード
  public $chaos_sub_role_limit_hard_list = array(
    'decide', 'plague', 'counter_decide', 'dropout', 'good_luck', 'bad_luck', 'authority',
    'reduce_voter', 'upper_voter', 'downer_voter', 'critical_voter', 'random_voter', 'rebel',
    'watcher', 'upper_luck', 'downer_luck', 'star', 'disfavor', 'critical_luck', 'random_luck',
    'strong_voice', 'normal_voice', 'weak_voice', 'upper_voice', 'downer_voice', 'inside_voice',
    'outside_voice', 'random_voice', 'mind_open', 'wisp', 'black_wisp', 'spell_wisp',
    'foughten_wisp', 'gold_wisp');

  //お祭り村専用配役テーブル
  public $festival_role_list = array(
     8 => array('human' => 2, 'mage' => 1, 'necromancer' => 1, 'wolf' => 1, 'mad' => 1, 'whisper_mad' => 1, 'fox' => 1),
     9 => array('human' => 3, 'guard' => 3, 'wolf' => 2, 'chiroptera' => 1),
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
  public $duel_fix_list = array(); //固定配役
  public $duel_rate_list = array('assassin' => 11, 'wolf' => 4, 'trap_mad' => 5); //配役比率

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
      if($user_count >= 15) $duel_fix_list['sweet_fairy'] = 1;
      if($user_count >= 20) $duel_fix_list['enchant_mad'] = 1;
      if($user_count >= 25){
	$duel_fix_list['sirius_wolf'] = 1;
	$duel_fix_list['moon_cupid'] = 1;
      }
      if($user_count >= 30) $duel_fix_list['quiz'] = 1;
      $duel_rate_list = array('select_assassin' => 5, 'wolf' => 3, 'self_cupid' => 1,
			      'mind_cupid' => 4, 'triangle_cupid' => 1);
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
      if($role_list['mind_cupid'] > 3){
	$role_list['mind_cupid']--;
	$role_list['exchange_angel']++;
      }
      if($role_list['mind_cupid'] > 3){
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
}
