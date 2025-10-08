<?php
/*
  ◆没落者 (downer_voter)
  ○仕様
  ・投票数：-1 (5日目以降)
*/
RoleLoader::LoadFile('authority');
class Role_downer_voter extends Role_authority {
  protected function IgnoreFilterVoteDo() {
    return DB::$ROOM->date < 5;
  }

  protected function GetVoteDoCount() {
    return -1;
  }
}
