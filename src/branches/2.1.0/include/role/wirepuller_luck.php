<?php
/*
  ◆入道 (wirepuller_luck)
  ○仕様
  ・投票数：+2 (付加者生存)
  ・得票数：+3 (付加者全滅)
*/
class Role_wirepuller_luck extends Role {
  protected function IgnoreAbility() { return DB::$ROOM->date < 2; }

  function FilterVoteDo(&$number) {
    if ($this->IsLivePartner()) $number += 2;
  }

  function FilterVotePoll(&$number) {
    if (! $this->IsLivePartner()) $number += 3;
  }
}
