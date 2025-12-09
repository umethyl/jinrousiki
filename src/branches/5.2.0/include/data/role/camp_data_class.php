<?php
//-- 基本陣営リスト (Camp/Base) --//
class BaseCamp {
  const HUMAN	= 'human';
  const WOLF	= 'wolf';
  const FOX	= 'fox';
  const LOVERS	= 'lovers';
  const QUIZ	= 'quiz';
  const VAMPIRE	= 'vampire';
}

//-- 陣営リスト (Camp) --//
class Camp extends BaseCamp {
  const CUPID		= 'cupid';
  const CHIROPTERA	= 'chiroptera';
  const OGRE		= 'ogre';
  const DUELIST		= 'duelist';
  const TENGU		= 'tengu';
  const MANIA		= 'mania';
}

//-- 陣営グループリスト (Camp/Group) --//
final class CampGroup extends Camp {
  const MAGE		= 'mage';
  const NECROMANCER	= 'necromancer';
  const MEDIUM		= 'medium';
  const PRIEST		= 'priest';
  const GUARD		= 'guard';
  const COMMON		= 'common';
  const POISON		= 'poison';
  const POISON_CAT	= 'poison_cat';
  const PHARMACIST	= 'pharmacist';
  const ASSASSIN	= 'assassin';
  const MIND_SCANNER	= 'mind_scanner';
  const JEALOUSY	= 'jealousy';
  const BROWNIE		= 'brownie';
  const WIZARD		= 'wizard';
  const DOLL		= 'doll';
  const ESCAPER		= 'escaper';
  const MAD		= 'mad';
  const CHILD_FOX	= 'child_fox';
  const DEPRAVER	= 'depraver';
  const ANGEL		= 'angel';
  const FAIRY		= 'fairy';
  const YAKSA		= 'yaksa';
  const AVENGER		= 'avenger';
  const PATRON		= 'patron';
  const UNKNOWN_MANIA	= 'unknown_mania';
}

//-- 勝利陣営リスト (Camp/Win) --//
final class WinCamp extends BaseCamp {
  const HUMAN_QUIZ	= 'human_quiz';
  const WOLF_QUIZ	= 'wolf_quiz';
  const FOX_HUMAN	= 'fox1';
  const FOX_WOLF	= 'fox2';
  const FOX_QUIZ	= 'fox_quiz';
  const DRAW		= 'draw';
  const QUIZ_DEAD	= 'quiz_dead';
  const VANISH		= 'vanish';
  const UNFINISHED	= 'unfinished';
  const NONE		= 'none';
}
