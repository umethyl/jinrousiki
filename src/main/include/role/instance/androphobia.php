<?php
/*
  ◆男性恐怖症 (androphobia)
  ○仕様
  ・ショック死：男性に投票
*/
RoleLoader::LoadFile('chicken');
class Role_androphobia extends Role_chicken {
  protected function IsSuddenDeath() {
    return Sex::IsMale($this->GetVoteKillUser());
  }

  protected function GetSuddenDeathType() {
    return 'ANDROPHOBIA';
  }
}
