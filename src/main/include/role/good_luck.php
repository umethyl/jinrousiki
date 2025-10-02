<?php
/*
  ◆幸運 (good_luck)
  ○仕様
  ・処刑者決定：除外 (自分)
*/
RoleManager::LoadFile('decide');
class Role_good_luck extends Role_decide {
  public $vote_day_type = 'self';

  public function DecideVoteKill() {
    if ($this->IsVoteKill()) return;

    $stack =& $this->GetVotePossible();
    $key = array_search($this->GetStack(), $stack);
    if ($key === false) return;

    unset($stack[$key]);
    //候補が一人になった場合は処刑者決定
    if (count($stack) == 1) $this->SetVoteKill(array_shift($stack));
  }
}
