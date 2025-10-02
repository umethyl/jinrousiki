<?php
/*
  ◆わらしべ長者 (upper_voter)
  ○仕様
  ・投票数：+1 (5日目以降)
*/
RoleManager::LoadFile('authority');
class Role_upper_voter extends Role_authority {
  public function IgnoreFilterVoteDo() {
    return DB::$ROOM->date < 5;
  }
}
