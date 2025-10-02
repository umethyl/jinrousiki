<?php
/*
  ◆掃除屋 (sweep_assassin)
  ○仕様
  ・投票：キャンセル投票不可
*/
RoleManager::LoadFile('assassin');
class Role_sweep_assassin extends Role_assassin {
  function ExistsActionFilter(array $list) {
    unset($list[$this->not_action]);
    return $list;
  }

  function SetVoteNightFilter() { $this->SetStack(null, 'not_action'); }
}
