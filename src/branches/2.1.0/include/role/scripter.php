<?php
/*
  ◆執筆者 (scripter)
  ○仕様
  ・投票数：+1 (5日目以降)
*/
class Role_scripter extends Role {
  public $ability = 'ability_scripter';

  function OutputResult() {
    if ($this->IsActive()) RoleHTML::OutputAbilityResult($this->ability, null);
  }

  function FilterVoteDo(&$number) {
    if ($this->IsActive()) $number++;
  }

  private function IsActive() { return DB::$ROOM->date > 4; }
}
