<?php
//-- 配役設定(決闘村) --//
class DuelConfig {
  //-- 固定配役追加モード --//
  /*
    fix    : 固定枠
    count  : 人口依存固定枠 (人数 => 配役)
    rate   : 比率
    calib  : 補正 (役職 => [補正役職 => 出現数]
  */
  public static $cast_list = [
    'a' => ['fix'   => [],
	    'count' => [],
	    'rate'  => ['assassin' => 11, 'wolf' => 4, 'trap_mad' => 5],
	    'calib' => []
           ],
    'b' => ['fix'   => ['triangle_cupid' => 1],
	    'count' => [20 => ['poison_jealousy' => 1, 'moon_cupid' => 1],
			25 => ['quiz' => 1]],
	    'rate'  => ['poison' => 5, 'chain_poison' => 10, 'poison_wolf' => 5, 'poison_fox' => 1],
	    'calib' => []
           ],
    'c' => ['fix'   => [],
	    'count' => [15 => ['sweet_fairy' => 1],
			20 => ['enchant_mad' => 1],
			25 => ['sirius_wolf' => 1, 'moon_cupid' => 1],
			30 => ['quiz' => 1]],
	    'rate' => ['select_assassin' => 5, 'wolf' => 3, 'self_cupid' => 1,
		       'mind_cupid' => 4, 'triangle_cupid' => 1],
	    'calib' => ['self_cupid' => ['dummy_chiroptera' => 1],
			'mind_cupid' => ['exchange_angel' => 3, 'sweet_cupid' => 3],
			'wolf'       => ['silver_wolf' => 2]]
           ],
    'd' => ['fix'   => [],
	    'count' => [16 => ['voodoo_fox' => 1],
			20 => ['voodoo_killer' => 1, 'cursed_wolf' => 1, 'cursed_angel' => 1],
			25 => ['cursed_fox' => 1]],
	    'rate'  => ['mage' => 8, 'spell_wolf' => 4, 'voodoo_mad' => 5,
			'cursed_chiroptera' => 1],
	    'calib' => []
           ],
    'e' => ['fix'   => [],
	    'count' => [15 => ['east_ogre' => 1, 'west_ogre' => 1,
			       'north_ogre' => 1, 'south_ogre' => 1],
			20 => ['power_ogre' => 1]],
	    'rate'  => ['reflect_guard' => 1, 'tiger_wolf' => 5, 'ogre' => 5, 'orange_ogre' => 10],
	    'calib' => ['orange_ogre' => ['bacchus_medium' => 5]]
           ],
    'f' => ['fix'   => [],
	    'count' => [20 => ['doom_escaper' => 1],
			25 => ['revive_cupid' => 1, 'doom_duelist' => 1]],
	    'rate'  => ['doom_assassin' => 11, 'doom_wolf' => 4, 'trap_mad' => 4, 'doom_fox' => 1],
	    'calib' => []
           ],
    'g' => ['fix'   => [],
	    'count' => [],
	    'rate'  => ['escaper' => 4, 'chiroptera' => 4, 'mist_wolf' => 4, 'miasma_mad' => 3,
			'follow_vampire' => 4, 'sacrifice_cupid' => 2],
	    'calib' => []
           ],
    'h' => ['fix'   => [],
	    'count' => [],
	    'rate'  => ['hunter_guard' => 10, 'wolf' => 4, 'amaze_mad' => 1, 'agitate_mad' => 1,
			'critical_mad' => 1, 'trap_mad' => 4, 'trap_fox' => 1],
	    'calib' => []
           ],
    'i' => ['fix'   => [],
	    'count' => [],
	    'rate'  => ['dummy_guard' => 3, 'psycho_escaper' => 1, 'miasma_wolf' => 4,
			'dream_eater_mad' => 4, 'fairy' => 6],
	    'calib' => ['fairy' => ['spring_fairy' => 1, 'summer_fairy' => 1, 'autumn_fairy' => 1,
				    'winter_fairy' => 1, 'greater_fairy' => 1, 'grass_fairy' => 1]]
           ],
    'j' => ['fix'   => ['sex_mage' => 1, 'gender_fairy' => 1],
	    'count' => [20 => ['incubus_vampire' => 1, 'succubus_vampire' => 1]],
	    'rate'  => ['incubus_escaper' => 5, 'succubus_escaper' => 5, 'wolf' => 3,
			'nephila_cupid' => 1],
	    'calib' => []
           ],
    'k' => ['fix'   => ['cure_pharmacist' => 1],
	    'count' => [],
	    'rate'  => ['tough' => 1, 'doom_assassin' => 7, 'wolf' => 4, 'miasma_mad' => 2,
			'follow_mad' => 3, 'seiren_mad' => 1],
	    'calib' => []
           ],
    'l' => ['fix'   => [],
	    'count' => [],
	    'rate'  => ['grave_guard' => 1, 'reverse_assassin' => 6, 'doom_cat' => 1,
			'missfire_cat' => 1, 'poison_cat' => 1, 'eclipse_cat' => 1,
			'wolf' => 3, 'resist_wolf' => 1, 'grave_mad' => 5],
	    'calib' => []
           ],
  ];
}
