<?php
/*
  ◆痛恨 (critical_luck)
  ○仕様
  ・得票数：+100 (5% / 天候「烈日」)
*/
RoleManager::LoadFile('upper_luck');
class Role_critical_luck extends Role_upper_luck {
  public function IgnoreFilterVotePoll() {
    return ! DB::$ROOM->IsEvent('critical') && ! Lottery::Percent(5);
  }

  public function GetVotePollCount() {
    return 100;
  }
}
