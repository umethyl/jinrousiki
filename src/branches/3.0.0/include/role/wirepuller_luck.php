<?php
/*
  ◆入道 (wirepuller_luck)
  ○仕様
  ・表示：2 日目以降
  ・投票数：+2 (付加者生存)
  ・得票数：+3 (付加者全滅)
*/
RoleManager::LoadFile('authority');
class Role_wirepuller_luck extends Role_authority {
  public $mix_in = array('upper_luck');

  protected function IgnoreAbility() {
    return DB::$ROOM->date < 2;
  }

  public function IgnoreFilterVoteDo() {
    return ! $this->IsLivePartner();
  }

  public function GetVoteDoCount() {
    return 2;
  }

  public function IgnoreFilterVotePoll() {
    return $this->IsLivePartner();
  }

  public function GetVotePollCount() {
    return 3;
  }
}
