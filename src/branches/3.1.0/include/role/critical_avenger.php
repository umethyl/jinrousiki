<?php
/*
  ◆狂骨 (critical_avenger)
  ○仕様
*/
RoleLoader::LoadFile('avenger');
class Role_critical_avenger extends Role_avenger {
  public $mix_in = array('critical_mad');

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }
}
