<?php
/*
  ◆傍観者 (watcher)
  ○仕様
  ・投票数：0 固定
*/
RoleLoader::LoadFile('authority');
class Role_watcher extends Role_authority {
  protected function GetVoteDoCount() {
    return 0;
  }

  protected function EnableAddVoteDoCount() {
    return false;
  }
}
