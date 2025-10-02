<?php
/*
  ◆会心 (critical_voter)
  ○仕様
  ・投票数：+100 (5% / 天候「烈日」)
*/
RoleManager::LoadFile('authority');
class Role_critical_voter extends Role_authority {
  public function IgnoreFilterVoteDo() {
    return ! DB::$ROOM->IsEvent('critical') && ! Lottery::Percent(5);
  }

  public function GetVoteDoCount() {
    return 100;
  }
}
