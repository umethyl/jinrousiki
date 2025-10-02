<?php
/*
  ◆光妖精 (light_fairy)
  ○仕様
  ・悪戯：迷彩 (公開者)
*/
RoleManager::LoadFile('fairy');
class Role_light_fairy extends Role_fairy {
  public $bad_status = 'mind_open';

  function FairyAction(User $user) {
    $target = $this->GetWolfTarget();
    if (! $target->wolf_killed || ! $target->IsSame($user)) return false;
    $this->AddStack($this->GetProperty('bad_status'), 'event');
  }
}
