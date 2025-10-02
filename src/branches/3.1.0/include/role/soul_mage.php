<?php
/*
  ◆魂の占い師 (soul_mage)
  ○仕様
  ・占い：役職判定 (憑依キャンセルあり)
*/
RoleLoader::LoadFile('mage');
class Role_soul_mage extends Role_mage {
  protected function GetMageResult(User $user) {
    $this->MagePossessedCancel($user);
    return $user->main_role;
  }
}
