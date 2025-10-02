<?php
/*
  ◆一日村長 (day_voter)
  ○仕様
  ・表示：当日限定
  ・投票数：+1 (当日限定)
*/
RoleLoader::LoadFile('authority');
class Role_day_voter extends Role_authority {
  protected function IgnoreAbility() {
    return false === $this->IsDoom();
  }

  protected function IgnoreFilterVoteDo() {
    return $this->IgnoreAbility();
  }
}
