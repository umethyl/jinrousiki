<?php
/*
  ◆一言主神 (critical_mage)
  ○仕様
  ・占い：痛恨付与
*/
RoleManager::LoadFile('mage');
class Role_critical_mage extends Role_mage {
  protected function MageAction(User $user) {
    if ($user->IsLive(true) && ! $user->IsAvoid()) {
      $user->AddRole('critical_luck');
    }
  }
}
