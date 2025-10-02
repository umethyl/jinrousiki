<?php
/*
  ◆燕返し (counter_decide)
  ○仕様
  ・処刑者決定：自分の投票先 (自身と投票先が最多得票者)
*/
RoleLoader::LoadFile('decide');
class Role_counter_decide extends Role_decide {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ADD;
  }

  public function DecideVoteKill() {
    if ($this->IsVoteKill()) return;

    $stack = $this->GetVotePossible();
    foreach ($this->GetStack() as $actor => $target) {
      if (in_array($actor, $stack) && in_array($target, $stack)) {
	$this->SetVoteKill($this->GetCounterDecideTarget($actor, $target));
      }
    }
  }

  //処刑対象者決定 (相互最多得票者)
  protected function GetCounterDecideTarget($actor, $target) {
    return $target;
  }
}
