<?php
/*
  ◆亡霊嬢 (ghost_common)
  ○仕様
  ・人狼襲撃：小心者
*/
RoleManager::LoadFile('common');
class Role_ghost_common extends Role_common {
  public function WolfEatCounter(User $user) {
    $user->AddRole('chicken');
  }
}
