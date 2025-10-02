<?php
/*
  ◆ウサギ (rabbit)
  ○仕様
  ・ショック死：無得票
*/
RoleManager::LoadFile('chicken');
class Role_rabbit extends Role_chicken {
  public $sudden_death = 'RABBIT';

  public function IsSuddenDeath() {
    return $this->GetVotedCount() == 0;
  }
}
