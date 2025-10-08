<?php
/*
  ◆夢語部 (dummy_mania)
  ○仕様
  ・役職表示：覚醒者
  ・コピー：特殊
  ・変化：劣化種
*/
RoleLoader::LoadFile('soul_mania');
class Role_dummy_mania extends Role_soul_mania {
  public $display_role = 'soul_mania';

  protected function GetCopiedRole() {
    return 'copied_teller';
  }

  protected function GetDelayCopyList() {
    return [
      CampGroup::HUMAN		=> 'suspect',
      CampGroup::MAGE		=> 'dummy_mage',
      CampGroup::NECROMANCER	=> 'dummy_necromancer',
      CampGroup::MEDIUM		=> 'eclipse_medium',
      CampGroup::PRIEST		=> 'dummy_priest',
      CampGroup::GUARD		=> 'dummy_guard',
      CampGroup::COMMON		=> 'dummy_common',
      CampGroup::POISON		=> 'dummy_poison',
      CampGroup::POISON_CAT	=> 'eclipse_cat',
      CampGroup::PHARMACIST	=> 'centaurus_pharmacist',
      CampGroup::ASSASSIN	=> 'eclipse_assassin',
      CampGroup::MIND_SCANNER	=> 'dummy_scanner',
      CampGroup::JEALOUSY	=> 'critical_jealousy',
      CampGroup::BROWNIE	=> 'maple_brownie',
      CampGroup::WIZARD		=> 'astray_wizard',
      CampGroup::DOLL		=> 'silver_doll',
      CampGroup::ESCAPER	=> 'stargazer_escaper',
      CampGroup::WOLF		=> 'emperor_wolf',
      CampGroup::MAD		=> 'immolate_mad',
      CampGroup::FOX		=> 'immolate_fox',
      CampGroup::CHILD_FOX	=> 'critical_fox',
      CampGroup::DEPRAVER	=> 'silver_depraver',
      CampGroup::CUPID		=> 'snow_cupid',
      CampGroup::ANGEL		=> 'cursed_angel',
      CampGroup::QUIZ		=> 'quiz',
      CampGroup::VAMPIRE	=> 'scarlet_vampire',
      CampGroup::CHIROPTERA	=> 'dummy_chiroptera',
      CampGroup::FAIRY		=> 'mirror_fairy',
      CampGroup::OGRE		=> 'incubus_ogre',
      CampGroup::YAKSA		=> 'succubus_yaksa',
      CampGroup::DUELIST	=> 'sea_duelist',
      CampGroup::AVENGER	=> 'cute_avenger',
      CampGroup::PATRON		=> 'critical_patron',
      CampGroup::TENGU		=> 'eclipse_tengu'
    ];
  }
}
