<?php
/*
  ◆蛻庵 (monk_fox)
  ○仕様
  ・霊能：通常
*/
RoleManager::LoadFile('child_fox');
class Role_monk_fox extends Role_child_fox {
  public $mix_in = array('necromancer');
  public $action = null;
  public $result = 'MONK_FOX_RESULT';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  //霊能
  public function Necromancer(User $user, $flag) {
    return ($flag || Lottery::Percent(30)) ? 'stolen' : $this->DistinguishNecromancer($user);
  }
}
