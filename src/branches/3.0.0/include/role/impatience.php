<?php
/*
  ◆短気 (impatience)
  ○仕様
  ・ショック死：再投票
  ・処刑者決定：決定者相当 (優先順位低め)
*/
RoleManager::LoadFile('chicken');
class Role_impatience extends Role_chicken {
  public $mix_in = array('decide');
  public $vote_day_type = 'target';
  public $sudden_death  = 'IMPATIENCE';

  public function IsSuddenDeath() {
    return ! $this->IsVoteKill();
  }
}
