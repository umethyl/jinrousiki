<?php
/*
  ◆流星天狗 (meteor_tengu)
  ○仕様
  ・神通力：神隠し
*/
RoleLoader::LoadFile('tengu');
class Role_meteor_tengu extends Role_tengu {
  protected function TenguKill(User $user) {
    if (! RoleUser::IsAvoid($user)) {
      DB::$USER->Kill($user->id, DeadReason::TENGU_KILLED);
    }
  }
}
