<?php
/*
  ◆一発屋 (downer_luck)
  ○仕様
  ・得票数：-4 (2日目) / +2 (3日目以降)
*/
RoleManager::LoadFile('upper_luck');
class Role_downer_luck extends Role_upper_luck {
  public function GetVotePollCount() {
    return DB::$ROOM->IsDate(2) ? -4 : 2;
  }
}
