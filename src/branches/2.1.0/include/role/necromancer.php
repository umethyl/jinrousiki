<?php
/*
  ◆霊能者 (necromancer)
  ○仕様
  ・霊能：通常
*/
class Role_necromancer extends Role {
  protected function OutputResult() {
    if (DB::$ROOM->date > 2) $this->OutputAbilityResult(strtoupper($this->role) . '_RESULT');
  }

  //霊能
  function Necromancer(User $user, $flag) {
    return $flag ? 'stolen' : $user->DistinguishNecromancer();
  }
}
