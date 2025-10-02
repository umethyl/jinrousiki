<?php
/*
  ◆蝕巫女 (eclipse_medium)
  ○仕様
  ・ショック死：再投票
*/
RoleManager::LoadFile('medium');
class Role_eclipse_medium extends Role_medium {
  public $mix_in = 'chicken';
  public $display_role = 'medium';
  public $sudden_death = 'SEALED';

  public function IgnoreSuddenDeath() {
    return ! $this->IsRealActor() || $this->GetActor()->IsAvoidLovers(true);
  }

  public function IsSuddenDeath() {
    return ! $this->IsVoteKill();
  }
}
