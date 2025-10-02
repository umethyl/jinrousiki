<?php
/*
  ◆自信家 (nervy)
  ○仕様
  ・ショック死：同一陣営に投票
*/
RoleLoader::LoadFile('chicken');
class Role_nervy extends Role_chicken {
  protected function IsSuddenDeath() {
    return $this->GetActor()->IsWinCamp($this->GetVoteKillUser()->GetWinCamp());
  }

  protected function GetSuddenDeathType() {
    return 'NERVY';
  }
}
