<?php
/*
  ◆蛻庵 (monk_fox)
  ○仕様
  ・霊能：通常
*/
RoleManager::LoadFile('child_fox');
class Role_monk_fox extends Role_child_fox {
  public $mix_in = null;
  public $action = null;
  public $result = 'MONK_FOX_RESULT';

  //霊能
  function Necromancer(User $user, $flag) {
    return $flag || Lottery::Percent(30) ? 'stolen' : $user->DistinguishNecromancer();
  }
}
