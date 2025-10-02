<?php
/*
  ◆物真似師 (mimic_wizard)
  ○仕様
  ・魔法：占い師 (50%) + 霊能
  ・占い：失敗
  ・霊能：通常 (50%)
*/
RoleManager::LoadFile('wizard');
class Role_mimic_wizard extends Role_wizard {
  public $mix_in = array('mage');
  public $wizard_list = array('mage' => 'MAGE_DO', 1 => 'MAGE_DO');
  public $result_list = array('MAGE_RESULT', 'MIMIC_WIZARD_RESULT');

  public function Mage(User $user) {
    $this->IsJammer($user);
    $this->SaveMageResult($user, 'failed', 'MAGE_RESULT');
  }

  public function Necromancer(User $user, $flag) {
    if (DB::$ROOM->date < 3) return;

    if (DB::$ROOM->IsEvent('full_wizard')) {
      $failed = false;
    } elseif (DB::$ROOM->IsEvent('debilitate_wizard')) {
      $failed = true;
    } else {
      $failed = Lottery::Bool();
    }

    if ($flag || $failed) {
      $result = 'stolen';
    } else {
      $result = RoleManager::GetClass('necromancer')->DistinguishNecromancer($user);
    }
    DB::$ROOM->ResultAbility('MIMIC_WIZARD_RESULT', $result, $user->GetName());
  }
}
