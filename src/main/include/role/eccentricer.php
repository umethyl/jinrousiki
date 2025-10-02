<?php
/*
  ◆傾奇者 (eccentricer)
  ○仕様
  ・投票数：+1 (4日目まで)
*/
class Role_eccentricer extends Role {
  public $ability = 'muster_ability';

  function OutputResult() {
    if ($this->IsLost()) RoleHTML::OutputAbilityResult($this->ability, null);
  }

  function FilterVoteDo(&$number) {
    if (! $this->IsLost()) $number++;
  }

  private function IsLost() { return DB::$ROOM->date > 4; }
}
