<?php
/*
  ◆一日村長 (day_voter)
  ○仕様
  ・表示：当日限定
  ・投票数：+1 (当日限定)
*/
class Role_day_voter extends Role {
  protected function IgnoreAbility() {
    return ! $this->IsDoom();
  }

  public function FilterVoteDo(&$count) {
    if ($this->IsDoom()) $count++;
  }
}
