<?php
/*
  ◆ひんな持ち (occupied_luck)
  ○仕様
  ・得票数：+1 (付加者生存) / +3 (付加者全滅)
*/
class Role_occupied_luck extends Role {
  protected function IgnoreAbility() { return DB::$ROOM->date < 2; }

  function FilterVotePoll(&$count) {
    $count += $this->IsLivePartner() ? 1 : 3;
  }
}
