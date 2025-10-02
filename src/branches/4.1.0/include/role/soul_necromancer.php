<?php
/*
  ◆雲外鏡 (soul_necromancer)
  ○仕様
  ・霊能：役職
*/
RoleLoader::LoadFile('necromancer');
class Role_soul_necromancer extends Role_necromancer {
  public $result = RoleAbility::SOUL_NECROMANCER;

  public function Necromancer(User $user, $flag) {
    return (true === $flag) ? 'stolen' : $user->main_role;
  }
}
