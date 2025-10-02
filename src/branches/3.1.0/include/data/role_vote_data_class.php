<?php
//-- 定数リスト (Role/StackVoteKill) --//
class RoleStackVoteKill {
  const ACTOR	= 'actor';
  const TARGET	= 'target';
  const ADD	= 'add';
  const INIT	= 'init';
  const ETC	= 'etc';
}

//-- 定数リスト (Role/ActionDate) --//
class RoleActionDate {
  const FIRST = 'first';
  const AFTER = 'after';
}

//-- 定数リスト (Role/Ability) --//
class RoleAbility {
  const MAGE			= 'MAGE_RESULT';
  const VOODOO_KILLER		= 'VOODOO_KILLER_SUCCESS';
  const NECROMANCER		= 'NECROMANCER_RESULT';
  const SOUL_NECROMANCER	= 'SOUL_NECROMANCER_RESULT';
  const PSYCHO_NECROMANCER	= 'PSYCHO_NECROMANCER_RESULT';
  const EMBALM_NECROMANCER	= 'EMBALM_NECROMANCER_RESULT';
  const EMISSARY_NECROMANCER	= 'EMISSARY_NECROMANCER_RESULT';
  const ATTEMPT_NECROMANCER	= 'ATTEMPT_NECROMANCER_RESULT';
  const DUMMY_NECROMANCER	= 'DUMMY_NECROMANCER_RESULT';
  const MEDIUM			= 'MEDIUM_RESULT';
  const PRIEST			= 'PRIEST_RESULT';
  const BISHOP_PRIEST		= 'BISHOP_PRIEST_RESULT';
  const DOWSER_PRIEST		= 'DOWSER_PRIEST_RESULT';
  const WEATHER_PRIEST		= 'WEATHER_PRIEST_RESULT';
  const CRISIS_PRIEST		= 'CRISIS_PRIEST_RESULT';
  const HOLY_PRIEST		= 'HOLY_PRIEST_RESULT';
  const BORDER_PRIEST		= 'BORDER_PRIEST_RESULT';
  const DUMMY_PRIEST		= 'DUMMY_PRIEST_RESULT';
  const GUARD			= 'GUARD_SUCCESS';
  const HUNTED			= 'GUARD_HUNTED';
  const PENETRATION		= 'GUARD_PENETRATION';
  const REPORTER		= 'REPORTER_SUCCESS';
  const ANTI_VOODOO		= 'ANTI_VOODOO_SUCCESS';
  const REVIVE			= 'POISON_CAT_RESULT';
  const PHARMACIST		= 'PHARMACIST_RESULT';
  const ASSASSIN		= 'ASSASSIN_RESULT';
  const CLAIRVOYANCE		= 'CLAIRVOYANCE_RESULT';
  const PRIEST_JEALOUSY		= 'PRIEST_JEALOUSY_RESULT';
  const MIMIC_WIZARD		= 'MIMIC_WIZARD_RESULT';
  const SPIRITISM_WIZARD	= 'SPIRITISM_WIZARD_RESULT';
  const SEX_WOLF		= 'SEX_WOLF_RESULT';
  const SHARP_WOLF		= 'SHARP_WOLF_RESULT';
  const TONGUE_WOLF		= 'TONGUE_WOLF_RESULT';
  const FOX			= 'FOX_EAT';
  const CHILD_FOX		= 'CHILD_FOX_RESULT';
  const MONK_FOX		= 'MONK_FOX_RESULT';
  const VAMPIRE			= 'VAMPIRE_RESULT';
  const PATRON			= 'PATRON_RESULT';
  const TENGU_CAMP		= 'TENGU_CAMP_RESULT';
  const TENGU			= 'TENGU_RESULT';
  const PRIEST_TENGU		= 'PRIEST_TENGU_RESULT';
  const MANIA			= 'MANIA_RESULT';
  const SYMPATHY		= 'SYMPATHY_RESULT';
  const PRESAGE			= 'PRESAGE_RESULT';
}

//-- 定数リスト (Role/Vote/Target) --//
class RoleVoteTarget {
  const TRAP			= 'trap';
  const SNOW_TRAP		= 'snow_trap';
  const GUARD			= 'guard';
  const GATEKEEPER_GUARD	= 'gatekeeper_guard';
  const DUMMY_GUARD		= 'dummy_guard';
  const BARRIER_WIZARD		= 'barrier_wizard';
  const ESCAPER			= 'escaper';
  const SACRIFICE		= 'sacrifice';
  const ANTI_VOODOO		= 'anti_voodoo';
  const REVERSE_ASSASSIN	= 'reverse_assassin';
}

//-- 定数リスト (Role/Vote/Success) --//
class RoleVoteSuccess {
  //遅行発動
  const TRAPPED		= 'trapped';
  const FROSTBITE	= 'frostbite';
  const ASSASSIN	= 'assassin';
  const OGRE		= 'ogre';
  const VAMPIRE_KILL	= 'vampire_kill';
  const PHANTOM		= 'phantom';
  const POSSESSED	= 'possessed';

  //成功登録
  const GUARD		= 'guard_success';
  const VOODOO_KILLER	= 'voodoo_killer_success';
  const ANTI_VOODOO	= 'anti_voodoo_success';
}
