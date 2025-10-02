<?php
/*
  ◆執行者 (executor)
  ○仕様
  ・役職表示：村人
  ・処刑者決定：同一投票先 & 非村人
*/
class Role_executor extends Role {
  public $mix_in = array('decide');
  public $display_role = 'human';

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ADD;
  }

  public function DecideVoteKill() {
    if ($this->IsVoteKill() || ! is_array($this->GetStack())) return;

    $stack = array();
    foreach ($this->GetMaxVotedUname() as $uname) {
      if (! DB::$USER->ByRealUname($uname)->IsWinCamp(Camp::HUMAN)) {
	$stack[$uname] = true;
      }
    }

    if (count($stack) == 1) {
      $this->SetVoteKill(ArrayFilter::PickKey($stack));
    }
  }
}
