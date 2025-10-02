<?php
/*
  ◆流星天狗 (meteor_tengu)
  ○仕様
  ・神通力：神隠し
*/
RoleManager::LoadFile('tengu');
class Role_meteor_tengu extends Role_tengu {
  protected function TenguKill(User $user) {
    if (! $user->IsAvoid()) DB::$USER->Kill($user->id, 'TENGU_KILLED');
  }
}
