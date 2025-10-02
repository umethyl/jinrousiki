<?php
/*
  ◆気分屋 (random_voter)
  ○仕様
  ・投票数：-1 ～ +1
*/
RoleLoader::LoadFile('authority');
class Role_random_voter extends Role_authority {
  protected function GetVoteDoCount() {
    return Lottery::GetRange(-1, 1);
  }
}
