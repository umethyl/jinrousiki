<?php
//-- 定数リスト (Vote/ActionGroup) --//
final class VoteActionGroup {
  //夜投票初期化 / 常設
  public static $init = [
    VoteAction::MAGE,
    VoteAction::STEP_MAGE,
    VoteAction::VOODOO_KILLER,
    VoteAction::SCAN,
    VoteAction::WOLF,
    VoteAction::STEP_WOLF,
    VoteAction::SILENT_WOLF,
    VoteAction::JAMMER,
    VoteAction::VOODOO_MAD,
    VoteAction::STEP,
    VoteAction::VOODOO_FOX,
    VoteAction::CHILD_FOX,
    VoteAction::FAIRY,
    VoteAction::TENGU
  ];

  //夜投票初期化 / 一日目
  public static $init_first = [
    VoteAction::MANIA,
    VoteAction::DUELIST
  ];

  //夜投票初期化 / 二日目以降
  public static $init_after = [
    VoteAction::GUARD,
    VoteAction::STEP_GUARD,
    VoteAction::ANTI_VOODOO,
    VoteAction::REPORTER,
    VoteAction::STEP_SCAN,
    VoteAction::REVIVE,
    VoteAction::ASSASSIN,
    VoteAction::STEP_ASSASSIN,
    VoteAction::WIZARD,
    VoteAction::PLURAL_WIZARD,
    VoteAction::SPREAD_WIZARD,
    VoteAction::ESCAPE,
    VoteAction::DREAM,
    VoteAction::TRAP,
    VoteAction::POSSESSED,
    VoteAction::GRAVE,
    VoteAction::EXIT_DO,
    VoteAction::VAMPIRE,
    VoteAction::STEP_VAMPIRE,
    VoteAction::OGRE,
    VoteAction::DEATH_NOTE
  ];

  //足音
  public static $step = [
    VoteAction::STEP_MAGE,
    VoteAction::STEP_WOLF,
    VoteAction::STEP
  ];

  //足音 / 二日目以降
  public static $step_after = [
    VoteAction::STEP_GUARD,
    VoteAction::STEP_ASSASSIN,
    VoteAction::STEP_VAMPIRE
  ];

  //人狼
  public static $wolf = [
    VoteAction::WOLF,
    VoteAction::STEP_WOLF,
    VoteAction::SILENT_WOLF
  ];

  //呪術
  public static $voodoo = [
    VoteAction::VOODOO_MAD,
    VoteAction::VOODOO_FOX
  ];

  //占い
  public static $mage = [
    VoteAction::MAGE,
    VoteAction::CHILD_FOX,
    VoteAction::FAIRY,
    VoteAction::TENGU
  ];

  //追跡・透視
  public static $report = [
    VoteAction::REPORTER,
    VoteAction::SCAN,
    VoteAction::STEP_SCAN
  ];
}
