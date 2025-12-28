<?php
//-- 役職データベース (グループ) --//
final class RoleGroupData {
  //メイン役職のグループリスト (役職 => 所属グループ)
  //このリストの並び順に strpos() で判別する (毒系など、順番依存の役職があるので注意)
  public static $list = [
    //-- 神話マニア陣営 --//
    //鵺系
    'unknown_mania'	=> CampGroup::UNKNOWN_MANIA,
    'wirepuller_mania'	=> CampGroup::UNKNOWN_MANIA,
    'fire_mania'	=> CampGroup::UNKNOWN_MANIA,
    'sacrifice_mania'	=> CampGroup::UNKNOWN_MANIA,
    'resurrect_mania'	=> CampGroup::UNKNOWN_MANIA,
    'revive_mania'	=> CampGroup::UNKNOWN_MANIA,
    'lute_mania'	=> CampGroup::UNKNOWN_MANIA,
    'harp_mania'	=> CampGroup::UNKNOWN_MANIA,
    'mask_mania'	=> CampGroup::UNKNOWN_MANIA,
    'mania'		=> CampGroup::MANIA,		//神話マニア系
    //-- 蝙蝠陣営 --//
    'chiroptera'	=> CampGroup::CHIROPTERA,	//蝙蝠系
    'fairy'		=> CampGroup::FAIRY,		//妖精系
    //-- 鬼陣営 --//
    'ogre'		=> CampGroup::OGRE,		//鬼系
    'yaksa'		=> CampGroup::YAKSA,		//夜叉系
    //-- 決闘者陣営 --//
    'duelist'		=> CampGroup::DUELIST,		//決闘者系
    'avenger'		=> CampGroup::AVENGER,		//復讐者系
    'patron'		=> CampGroup::PATRON,		//後援者系
    //-- 天狗陣営 --//
    'tengu'		=> CampGroup::TENGU,		//天狗系
    //-- 恋人陣営 --//
    'cupid'		=> CampGroup::CUPID,		//キューピッド系
    'angel'		=> CampGroup::ANGEL,		//天使系
    //-- 出題者陣営 --//
    'quiz'		=> CampGroup::QUIZ,		//出題者系
    //-- 吸血鬼陣営 --//
    'vampire'		=> CampGroup::VAMPIRE,		//吸血鬼系
    //-- 妖狐陣営 --//
    //子狐系
    'child_fox'		=> CampGroup::CHILD_FOX,
    'sex_fox'		=> CampGroup::CHILD_FOX,
    'stargazer_fox'	=> CampGroup::CHILD_FOX,
    'monk_fox'		=> CampGroup::CHILD_FOX,
    'jammer_fox'	=> CampGroup::CHILD_FOX,
    'miasma_fox'	=> CampGroup::CHILD_FOX,
    'howl_fox'		=> CampGroup::CHILD_FOX,
    'vindictive_fox'	=> CampGroup::CHILD_FOX,
    'critical_fox'	=> CampGroup::CHILD_FOX,
    'fox'		=> CampGroup::FOX,		//妖狐系
    'depraver'		=> CampGroup::DEPRAVER,		//背徳者系
    //-- 人狼陣営 --//
    'wolf'		=> CampGroup::WOLF,		//人狼系
    'mad'		=> CampGroup::MAD,		//狂人系
    //-- 村人陣営 --//
    'necromancer'	=> CampGroup::NECROMANCER,	//霊能者系
    'medium'		=> CampGroup::MEDIUM,		//巫女系
    'jealousy'		=> CampGroup::JEALOUSY,		//橋姫系
    'priest'		=> CampGroup::PRIEST,		//司祭系
    //狩人系
    'guard'		=> CampGroup::GUARD,
    'anti_voodoo'	=> CampGroup::GUARD,
    'reporter'		=> CampGroup::GUARD,
    'common'		=> CampGroup::COMMON,		//共有者系
    'pharmacist'	=> CampGroup::PHARMACIST,	//薬師系
    'assassin'		=> CampGroup::ASSASSIN,		//暗殺者系
    'scanner'		=> CampGroup::MIND_SCANNER,	//さとり系
    'brownie'		=> CampGroup::BROWNIE,		//座敷童子系
    'wizard'		=> CampGroup::WIZARD,		//魔法使い系
    'servant'		=> CampGroup::SERVANT,		//従者系
    'doll'		=> CampGroup::DOLL,		//上海人形系
    'escaper'		=> CampGroup::ESCAPER,		//逃亡者系
    //占い師系
    'mage'		=> CampGroup::MAGE,
    'voodoo_killer'	=> CampGroup::MAGE,
    'cat'		=> CampGroup::POISON_CAT,	//猫又系
    'poison'		=> CampGroup::POISON,		//埋毒者系
  ];
}
