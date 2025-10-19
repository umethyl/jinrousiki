<?php
/*
  ◆一言主神 (critical_mage)
  ○仕様
  ・占い追加処理：痛恨付与
*/
RoleLoader::LoadFile('mage');
class Role_critical_mage extends Role_mage {
  protected function MageAction(User $user) {
    if ($user->IsLive(true) && false === RoleUser::Avoid($user)) {
      $user->AddRole('critical_luck');
    }
  }
}
