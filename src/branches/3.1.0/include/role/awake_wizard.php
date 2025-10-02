<?php
/*
  ◆比丘尼 (awake_wizard)
  ○仕様
  ・魔法：占い師・ひよこ鑑定士・占星術師 (30%) → 魂の占い師 (100%)
  ・天候：霧雨(占い師), 木枯らし(ひよこ鑑定士),  覚醒後は魂の占い師固定
  ・占い：失敗
  ・人狼襲撃耐性：無効 (一回限定)
*/
RoleLoader::LoadFile('wizard');
class Role_awake_wizard extends Role_wizard {
  public $mix_in = array('mage');

  protected function GetWizardResultList() {
    return array(RoleAbility::MAGE);
  }

  protected function GetWizardList() {
    if ($this->IsActorActive()) {
      if (Lottery::Percent(30)) {
	return array(
	  'mage'		=> VoteAction::MAGE,
	  'stargazer_mage'	=> VoteAction::MAGE,
	  'sex_mage'		=> VoteAction::MAGE
	);
      } else {
	return array(1 => VoteAction::MAGE);
      }
    } else {
      return array('soul_mage' => VoteAction::MAGE);
    }
  }

  public function WolfEatResist() {
    if (! $this->IsActorActive()) return false;

    $this->GetActor()->LostAbility();
    return true;
  }

  public function IsMageFailed() {
    return true;
  }
}
