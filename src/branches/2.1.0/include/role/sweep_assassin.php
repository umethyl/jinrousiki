<?php
/*
  ◆掃除屋 (sweep_assassin)
  ○仕様
  ・投票：キャンセル投票不可
*/
RoleManager::LoadFile('assassin');
class Role_sweep_assassin extends Role_assassin {
  function IsFinishVote(array $list) {
    unset($list[$this->not_action]);
    return parent::IsFinishVote($list);
  }

  function SetVoteNight() {
    parent::SetVoteNight();
    $this->SetStack(null, 'not_action');
  }
}
