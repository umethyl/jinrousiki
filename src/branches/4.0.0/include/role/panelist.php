<?php
/*
  ◆解答者 (panelist)
  ○仕様
  ・ショック死：出題者投票
  ・投票数：0
*/
RoleLoader::LoadFile('chicken');
class Role_panelist extends Role_chicken {
  public $mix_in = ['watcher'];

  protected function IsSuddenDeath() {
    return $this->GetVoteUser()->IsRole('quiz');
  }

  protected function GetSuddenDeathType() {
    return 'PANELIST';
  }
}
