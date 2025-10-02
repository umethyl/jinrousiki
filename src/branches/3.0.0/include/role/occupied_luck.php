<?php
/*
  ◆ひんな持ち (occupied_luck)
  ○仕様
  ・表示：2 日目以降
  ・得票数：+1 (付加者生存) / +3 (付加者全滅)
*/
RoleManager::LoadFile('upper_luck');
class Role_occupied_luck extends Role_upper_luck {
  protected function IgnoreAbility() {
    return DB::$ROOM->date < 2;
  }

  public function GetVotePollCount() {
    return $this->IsLivePartner() ? 1 : 3;
  }
}
