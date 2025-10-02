<?php
/*
  ◆雲外鏡 (soul_necromancer)
  ○仕様
  ・霊能：役職
*/
RoleManager::LoadFile('necromancer');
class Role_soul_necromancer extends Role_necromancer {
  public $result = 'SOUL_NECROMANCER_RESULT';

  public function Necromancer(User $user, $flag) {
    return $flag ? 'stolen' : $user->main_role;
  }
}
