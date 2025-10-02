<?php
/*
  ◆光妖精 (light_fairy)
  ○仕様
  ・悪戯：迷彩 (公開者)
*/
RoleLoader::LoadFile('fairy');
class Role_light_fairy extends Role_fairy {
  protected function GetBadStatus() {
    return 'mind_open';
  }

  protected function FairyAction(User $user) {
    $target = $this->GetWolfTarget();
    if (false === $target->wolf_killed || false === $target->IsSame($user)) {
      return false;
    }
    $this->AddStack($this->CallParent('GetBadStatus'), 'event');
  }
}
