<?php
/*
  ◆大天狗 (soul_tengu)
  ○仕様
  ・神通力：役職取得
*/
RoleManager::LoadFile('tengu');
class Role_soul_tengu extends Role_tengu {
  protected function IgnoreResult() {
    return false;
  }

  protected function OutputAddResult() {
    $this->OutputAbilityResult('TENGU_RESULT');
  }

  protected function IgnoreTenguTarget(User $user) {
    return false;
  }

  protected function TenguKill(User $user) {
    $this->SaveMageResult($user, $user->main_role, 'TENGU_RESULT');
  }
}
