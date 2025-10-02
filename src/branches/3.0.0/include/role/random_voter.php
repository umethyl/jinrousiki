<?php
/*
  ◆気分屋 (random_voter)
  ○仕様
  ・投票数：-1 ～ +1
*/
RoleManager::LoadFile('authority');
class Role_random_voter extends Role_authority {
  public function GetVoteDoCount() {
    return Lottery::GetRange(-1, 1);
  }
}
