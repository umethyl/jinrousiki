<?php
/*
  ◆傍観者 (watcher)
  ○仕様
  ・投票数：0
*/
RoleManager::LoadFile('authority');
class Role_watcher extends Role_authority {
  public function GetVoteDoCount() {
    return 0;
  }

  public function IsUpdateFilterVoteDo() {
    return true;
  }
}
