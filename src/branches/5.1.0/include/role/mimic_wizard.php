<?php
/*
  ◆物真似師 (mimic_wizard)
  ○仕様
  ・魔法：占い師 (50%) + 霊能
  ・天候：霧雨(成功率 100%), 木枯らし(成功率 0%)
  ・占い：失敗
  ・霊能：通常 (50%)
*/
RoleLoader::LoadFile('wizard');
class Role_mimic_wizard extends Role_wizard {
  public $mix_in = ['mage', 'necromancer'];

  protected function GetWizardResultList() {
    return [RoleAbility::MAGE, RoleAbility::MIMIC_WIZARD];
  }

  protected function GetWizardList() {
    return ['mage' => VoteAction::MAGE, 1 => VoteAction::MAGE];
  }

  public function FixMageFailed() {
    return true;
  }

  public function NecromancerWizard(User $user, $flag) {
    if (DB::$ROOM->date < 3) {
      return;
    }

    if (DB::$ROOM->IsEvent('full_wizard')) {
      $failed = false;
    } elseif (DB::$ROOM->IsEvent('debilitate_wizard')) {
      $failed = true;
    } else {
      $failed = Lottery::Bool();
    }

    if (true === $flag || true === $failed) {
      $result = 'stolen';
    } else {
      $result = $this->DistinguishNecromancer($user);
    }
    DB::$ROOM->StoreAbility(RoleAbility::MIMIC_WIZARD, $result, $user->GetName());
  }
}
