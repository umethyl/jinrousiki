<?php
/*
  ◆無精者 (reduce_voter)
  ○仕様
  ・投票数：-1
*/
RoleLoader::LoadFile('authority');
class Role_reduce_voter extends Role_authority {
  protected function GetVoteDoCount() {
    return -1;
  }
}
