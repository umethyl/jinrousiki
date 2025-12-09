<?php
/*
  ◆左道使い (astray_wizard)
  ○仕様
  ・能力結果：なし
  ・魔法：反魂師・月兎・呪術師・獏・雪女・冥狐・闇妖精
  ・天候：霧雨(反魂師), 木枯らし(闇妖精)
*/
RoleLoader::LoadFile('wizard');
class Role_astray_wizard extends Role_wizard {
  protected function IgnoreWizardResult() {
    return true;
  }

  protected function GetWizardList() {
    return [
      'reverse_assassin'	=> VoteAction::ASSASSIN,
      'jammer_mad'		=> VoteAction::JAMMER,
      'voodoo_mad'		=> VoteAction::VOODOO_MAD,
      'dream_eater_mad'		=> VoteAction::DREAM,
      'snow_trap_mad'		=> VoteAction::TRAP,
      'doom_fox'		=> VoteAction::ASSASSIN,
      'dark_fairy'		=> VoteAction::FAIRY
    ];
  }
}
