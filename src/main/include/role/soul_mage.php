<?php
/*
  ◆魂の占い師 (soul_mage)
  ○仕様
  ・占い：役職判定
*/
RoleManager::LoadFile('mage');
class Role_soul_mage extends Role_mage {
  function GetMageResult(User $user) {
    if (array_key_exists($user->uname, $this->GetStack('possessed'))) { //憑依キャンセル判定
      $user->possessed_cancel = true;
    }
    return $user->main_role;
  }
}
