<?php
/*
  ◆女性恐怖症 (gynophobia)
  ○仕様
  ・ショック死：女性に投票
*/
RoleLoader::LoadFile('chicken');
class Role_gynophobia extends Role_chicken {
  protected function IsSuddenDeath() {
    return Sex::IsFemale($this->GetVoteUser());
  }

  protected function GetSuddenDeathType() {
    return 'GYNOPHOBIA';
  }
}
