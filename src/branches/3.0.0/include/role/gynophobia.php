<?php
/*
  ◆女性恐怖症 (gynophobia)
  ○仕様
  ・ショック死：女性に投票
*/
RoleManager::LoadFile('chicken');
class Role_gynophobia extends Role_chicken {
  public $sudden_death = 'GYNOPHOBIA';

  public function IsSuddenDeath() {
    return $this->GetVoteUser()->IsFemale();
  }
}
