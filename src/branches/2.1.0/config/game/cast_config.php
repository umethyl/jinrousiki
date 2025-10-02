<?php
//-- 配役設定 --//
class CastConfig {
  /* 確率設定 */
  //役割希望制で希望が通る確率 (%) (身代わり君がいる場合は 100% にしても保証されない)
  const WISH_ROLE_RATE = 100;

  /* 配役テーブル */
  /*
    [ゲーム参加人数] => array([配役名1] => [配役名1の人数], [配役名2] => [配役名2の人数], ...),
    ゲーム参加人数と配役名の人数の合計が合わない場合はゲーム開始投票時にエラーが返る
  */

  //基本配役テーブル
  static $role_list = array(
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

  //お祭り村専用配役テーブル
  static $festival_role_list = array(
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

  //決闘村配役テーブル (実際は InitializeDuel() で初期化する)
  static $duel_fix_list  = array(); //固定配役
  static $duel_rate_list = array('assassin' => 11, 'wolf' => 4, 'trap_mad' => 5); //配役比率

  /* 役職出現人数 */
  //各役職の出現に必要な人数を設定する
  static $poison         = 20; //埋毒者 [村人2 → 埋毒者1、人狼1]
  static $assassin       = 22; //暗殺者 [村人2 → 暗殺者1、人狼1]
  static $wolf           = 20; //人狼追加 [村人1 → 人狼1]
  static $boss_wolf      = 18; //白狼 [人狼1 → 白狼]
  static $poison_wolf    = 20; //毒狼 (+ 薬師) [人狼1 → 毒狼1、村人1 → 薬師1]
  static $tongue_wolf    =  8; //舌禍狼 [人狼1 → 舌禍狼1]
  static $possessed_wolf =  8; //憑狼 [人狼1 → 憑狼1]
  static $sirius_wolf    =  8; //天狼 [人狼1 → 天狼1]
  static $fox            =  8; //妖狐追加 [村人1 → 妖狐1]
  static $child_fox      =  8; //子狐 [妖狐1 → 子狐1]
  static $cupid          = 16; //キューピッド [村人1 → キューピッド1]
  static $medium         = 20; //巫女 (+ 女神) [村人2 → 巫女1、女神1]
  static $mania          = 16; //神話マニア [村人1 → 神話マニア1]
  static $decide         = 16; //決定者 [兼任]
  static $authority      = 16; //権力者 [兼任]

  /* その他 */
  //身代わり君がならない役職グループのリスト (人狼・妖狐は常時対象外なので設定不要)
  static $disable_dummy_boy_role_list = array('poison');

  //役職置換モード (オプション名 => 置換先役職)
  static $replace_role_list = array(
    'replace_human' => 'escaper',
    'change_common' => 'leader_common',
    'change_mad'    => 'jammer_mad',
    'change_cupid'  => 'exchange_angel');

  /* 関数 */
  //決闘村の配役初期化処理
  static function InitializeDuel($user_count) {
    //-- 霊界自動公開オプションによる配役設定分岐 --//
    if (DB::$ROOM->IsOption('not_open_cast')) { //非公開
      //-- 埋毒決闘 --//
      $duel_fix_list = array();
      if ($user_count >= 20) {
	$duel_fix_list['poison_jealousy'] = 1;
	$duel_fix_list['moon_cupid'] = 1;
      }
      if ($user_count >= 25) $duel_fix_list['quiz'] = 1;
      $duel_rate_list = array('poison' => 5, 'chain_poison' => 10,
			      'poison_wolf' => 5, 'triangle_cupid' => 2);
    }
    elseif (DB::$ROOM->IsOption('auto_open_cast')) { //自動公開
      //-- 恋色決闘 --//
      $duel_fix_list = array();
      if ($user_count >= 15) $duel_fix_list['sweet_fairy'] = 1;
      if ($user_count >= 20) $duel_fix_list['enchant_mad'] = 1;
      if ($user_count >= 25) {
	$duel_fix_list['sirius_wolf'] = 1;
	$duel_fix_list['moon_cupid'] = 1;
      }
      if ($user_count >= 30) $duel_fix_list['quiz'] = 1;
      $duel_rate_list = array('select_assassin' => 5, 'wolf' => 3, 'self_cupid' => 1,
			      'mind_cupid' => 4, 'triangle_cupid' => 1);
    }
    else { //常時公開
      //-- 暗殺決闘 --//
      $duel_fix_list  = array();
      $duel_rate_list = array('assassin' => 11, 'wolf' => 4, 'trap_mad' => 5);
    }

    //結果を登録
    self::$duel_fix_list  = $duel_fix_list;
    self::$duel_rate_list = $duel_rate_list;
  }

  //決闘村の配役最終処理
  static function FinalizeDuel($user_count, &$role_list) {
    if (DB::$ROOM->IsOption('not_open_cast')) { //非公開
    }
    elseif (DB::$ROOM->IsOption('auto_open_cast')) { //自動公開
      if (@$role_list['self_cupid'] > 1) {
	$role_list['self_cupid']--;
	@$role_list['dummy_chiroptera']++;
      }
      if (@$role_list['mind_cupid'] > 3) {
	$role_list['mind_cupid']--;
	@$role_list['exchange_angel']++;
      }
      if (@$role_list['mind_cupid'] > 3) {
	$role_list['mind_cupid']--;
	@$role_list['sweet_cupid']++;
      }
      if (@$role_list['wolf'] > 2) {
	$role_list['wolf']--;
	@$role_list['silver_wolf']++;
      }
    }
    else { //常時公開
    }
  }
}
