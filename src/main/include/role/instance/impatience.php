<?php
/*
  ◆短気 (impatience)
  ○仕様
  ・ショック死：再投票
  ・処刑者決定：決定者相当 (優先順位低め)
*/
RoleLoader::LoadFile('chicken');
class Role_impatience extends Role_chicken {
  public $mix_in = ['decide'];

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::TARGET;
  }

  protected function IsSuddenDeath() {
    return false === $this->DetermineVoteKill();
  }

  protected function GetSuddenDeathType() {
    return 'IMPATIENCE';
  }
}
