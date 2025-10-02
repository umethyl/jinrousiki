<?php
/*
  ◆花妖精 (flower_fairy)
  ○仕様
  ・悪戯：死亡欄妨害 (花)
*/
RoleManager::LoadFile('fairy');
class Role_flower_fairy extends Role_fairy {
  public $result = 'FLOWERED';

  function FairyAction(User $user) {
    DB::$ROOM->ResultDead($user->GetName(), $this->result, Lottery::GetRange('A', 'Z'));
  }
}
