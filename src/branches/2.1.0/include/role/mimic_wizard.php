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
  public $mix_in = 'mage';
  public $wizard_list = array('mage' => 'MAGE_DO', 1 => 'MAGE_DO');
  public $result_list = array('MAGE_RESULT', 'MIMIC_WIZARD_RESULT');

  function Mage(User $user) {
    $this->IsJammer($user);
    $this->SaveMageResult($user, 'failed', 'MAGE_RESULT');
  }

  function Necromancer(User $user, $flag) {
    if (DB::$ROOM->date < 3) return;
    $failed = ! DB::$ROOM->IsEvent('full_wizard') &&
      (DB::$ROOM->IsEvent('debilitate_wizard') || mt_rand(0, 1) > 0);
    $result = $flag || $failed ? 'stolen' : $user->DistinguishNecromancer();
    $target = DB::$USER->GetHandleName($user->uname, true);
    DB::$ROOM->ResultAbility('MIMIC_WIZARD_RESULT', $result, $target);
  }
}
