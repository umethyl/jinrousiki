<?php
/*
  ◆蝕巫女 (eclipse_medium)
  ○仕様
  ・ショック死：再投票
*/
RoleLoader::LoadFile('medium');
class Role_eclipse_medium extends Role_medium {
  public $mix_in = ['chicken'];
  public $display_role = 'medium';

  protected function IgnoreSuddenDeath() {
    return $this->IgnoreSuddenDeathAvoid();
  }

  protected function IsSuddenDeath() {
    return false === $this->DetermineVoteKill();
  }

  protected function GetSuddenDeathType() {
    return 'SEALED';
  }
}
