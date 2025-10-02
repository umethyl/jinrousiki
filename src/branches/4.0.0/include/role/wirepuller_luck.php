<?php
/*
  ◆入道 (wirepuller_luck)
  ○仕様
  ・表示：2 日目以降
  ・投票数：+1 (付加者生存)
  ・得票数：+1 (付加者全滅)
*/
RoleLoader::LoadFile('authority');
class Role_wirepuller_luck extends Role_authority {
  public $mix_in = ['disfavor'];

  protected function IgnoreAbility() {
    return DB::$ROOM->date < 2;
  }

  protected function IgnoreFilterVoteDo() {
    return ! $this->IsLivePartner();
  }

  protected function IgnoreFilterVotePoll() {
    return $this->IsLivePartner();
  }
}
