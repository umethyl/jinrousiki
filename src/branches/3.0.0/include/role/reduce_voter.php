<?php
/*
  ◆無精者 (reduce_voter)
  ○仕様
  ・投票数：-1
*/
RoleManager::LoadFile('authority');
class Role_reduce_voter extends Role_authority {
  public function GetVoteDoCount() {
    return -1;
  }
}
