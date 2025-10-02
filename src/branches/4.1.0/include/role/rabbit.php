<?php
/*
  ◆ウサギ (rabbit)
  ○仕様
  ・ショック死：無得票
*/
RoleLoader::LoadFile('chicken');
class Role_rabbit extends Role_chicken {
  protected function IsSuddenDeath() {
    return $this->CountVotePollUser() == 0;
  }

  protected function GetSuddenDeathType() {
    return 'RABBIT';
  }
}
