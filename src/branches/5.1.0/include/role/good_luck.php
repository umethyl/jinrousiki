<?php
/*
  ◆幸運 (good_luck)
  ○仕様
  ・処刑者決定：除外 (自分)
*/
RoleLoader::LoadFile('decide');
class Role_good_luck extends Role_decide {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ACTOR;
  }

  public function DecideVoteKill() {
    if ($this->DetermineVoteKill()) {
      return;
    }

    $stack =& $this->GetVoteKillPossibleList();
    $key = array_search($this->GetStack(), $stack);
    if (false === $key) {
      return;
    }

    unset($stack[$key]);
    if (count($stack) == 1) { //候補が一人になった場合は処刑者決定
      $this->SetVoteKill(array_shift($stack));
    }
  }
}
