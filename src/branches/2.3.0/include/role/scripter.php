<?php
/*
  ◆執筆者 (scripter)
  ○仕様
  ・投票数：+1 (5日目以降)
*/
class Role_scripter extends Role {
  public $ability = 'ability_scripter';

  protected function IgnoreResult() {
    return ! $this->IsActive();
  }

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult($this->ability, null);
  }

  public function FilterVoteDo(&$count) {
    if ($this->IsActive()) $count++;
  }

  //能力発動判定
  private function IsActive() {
    return DB::$ROOM->date > 4;
  }
}
