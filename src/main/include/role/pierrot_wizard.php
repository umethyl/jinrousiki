<?php
/*
  ◆道化師 (pierrot_wizard)
  ○仕様
  ・魔法：魂の占い師・ひよこ鑑定士・暗殺(特殊)・草妖精・星妖精・花妖精・氷妖精・妖精(特殊)
  ・天候：霧雨(魂の占い師), 木枯らし(ひよこ鑑定士)
  ・暗殺：死の宣告 (2-10日後)
  ・悪戯：死亡欄妨害 (特殊)
*/
RoleLoader::LoadFile('wizard');
class Role_pierrot_wizard extends Role_wizard {
  public $mix_in = ['doom_assassin', 'flower_fairy'];

  protected function GetWizardResultList() {
    return [RoleAbility::MAGE];
  }

  protected function GetWizardList() {
    return [
      'soul_mage'	=> VoteAction::MAGE,
      1			=> VoteAction::ASSASSIN,
      2			=> VoteAction::FAIRY,
      'grass_fairy'	=> VoteAction::FAIRY,
      'star_fairy'	=> VoteAction::FAIRY,
      'flower_fairy'	=> VoteAction::FAIRY,
      'ice_fairy'	=> VoteAction::FAIRY,
      'sex_mage'	=> VoteAction::MAGE
    ];
  }

  protected function GetDoomAssassinDate() {
    return Lottery::GetRange(2, 10);
  }

  protected function GetFairyActionResult() {
    return 'PIERROT';
  }
}
