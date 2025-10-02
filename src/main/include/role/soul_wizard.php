<?php
/*
  ◆八卦見 (soul_wizard)
  ○仕様
  ・魔法：魂の占い師・精神鑑定士・ひよこ鑑定士・占星術師・騎士・死神・辻斬り・光妖精
  ・天候：霧雨(魂の占い師), 木枯らし(ひよこ鑑定士)
*/
RoleLoader::LoadFile('wizard');
class Role_soul_wizard extends Role_wizard {
  protected function GetWizardResultList() {
    return array(RoleAbility::MAGE, RoleAbility::GUARD, RoleAbility::HUNTED, RoleAbility::ASSASSIN);
  }

  protected function GetWizardList() {
    return array(
      'soul_mage'	=> VoteAction::MAGE,
      'psycho_mage'	=> VoteAction::MAGE,
      'stargazer_mage'	=> VoteAction::MAGE,
      'poison_guard'	=> VoteAction::GUARD,
      'doom_assassin'	=> VoteAction::ASSASSIN,
      'soul_assassin'	=> VoteAction::ASSASSIN,
      'light_fairy'	=> VoteAction::FAIRY,
      'sex_mage'	=> VoteAction::MAGE
    );
  }
}
