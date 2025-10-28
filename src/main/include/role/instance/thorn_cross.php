<?php
/*
  ◆荊十字 (thorn_cross)
  ○仕様
  ・ショック死：荊狼からの得票 + 確率 (80%)
*/
RoleLoader::LoadFile('chicken');
class Role_thorn_cross extends Role_chicken {
  protected function IsSuddenDeath() {
    foreach ($this->GetVotePollList() as $uname) {
      if (DB::$USER->ByRealUname($uname)->IsRole('thorn_wolf') && Lottery::Percent(80)) {
	return true;
      }
    }
    return false;
  }

  protected function GetSuddenDeathType() {
    return 'THORN';
  }
}
