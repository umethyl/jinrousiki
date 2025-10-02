<?php
/*
  ◆首領 (sacrifice_common)
  ○仕様
  ・身代わり：村人・蝙蝠
*/
RoleManager::LoadFile('common');
class Role_sacrifice_common extends Role_common {
  public $mix_in = array('protected');

  public function IsSacrifice(User $user) {
    return $user->IsRole('human', 'chiroptera');
  }
}
