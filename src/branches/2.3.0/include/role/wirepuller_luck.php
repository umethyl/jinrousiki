<?php
/*
  ◆入道 (wirepuller_luck)
  ○仕様
  ・表示：2 日目以降
  ・投票数：+2 (付加者生存)
  ・得票数：+3 (付加者全滅)
*/
class Role_wirepuller_luck extends Role {
  protected function IgnoreAbility() {
    return DB::$ROOM->date < 2;
  }

  public function FilterVoteDo(&$count) {
    if ($this->IsLivePartner()) $count += 2;
  }

  public function FilterVotePoll(&$count) {
    if (! $this->IsLivePartner()) $count += 3;
  }
}
