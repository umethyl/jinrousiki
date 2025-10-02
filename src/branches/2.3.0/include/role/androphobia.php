<?php
/*
  ◆男性恐怖症 (androphobia)
  ○仕様
  ・ショック死：男性に投票
*/
RoleManager::LoadFile('chicken');
class Role_androphobia extends Role_chicken {
  public $sudden_death = 'ANDROPHOBIA';

  public function IsSuddenDeath() {
    return $this->GetVoteUser()->IsMale();
  }
}
