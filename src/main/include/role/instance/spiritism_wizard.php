<?php
/*
  ◆交霊術師 (spiritism_wizard)
  ○仕様
  ・魔法：霊能者・雲外鏡・精神感応者・死化粧師・性別鑑定(オリジナル)
  ・天候：霧雨(雲外鏡), 木枯らし(性別鑑定)
  ・霊能：性別
*/
RoleLoader::LoadFile('wizard');
class Role_spiritism_wizard extends Role_wizard {
  public $action = null;

  protected function GetWizardResultList() {
    return [RoleAbility::SPIRITISM_WIZARD];
  }

  protected function GetWizardList() {
    return [
      'soul_necromancer',
      'necromancer',
      'psycho_necromancer',
      'embalm_necromancer',
      'sex_necromancer'
    ];
  }

  public function NecromancerWizard(User $user, $flag) {
    $role = $this->SetWizard();
    $type = RoleAbility::SPIRITISM_WIZARD;
    if ($role == ArrayFilter::Pop($this->GetWizardList())) {
      $result = (true === $flag) ? 'stolen' : Sex::Distinguish($user);
      DB::$ROOM->StoreAbility($type, $result, $user->GetName());
    } else {
      $stack = RoleManager::Stack();
      $stack->Get('necromancer_wizard')->$role = true;
      $stack->Set('necromancer_wizard_result', $type);
    }
  }
}
