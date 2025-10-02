<?php
/*
  ◆傾奇者 (eccentricer)
  ○仕様
  ・投票数：+1 (4日目まで)
*/
class Role_eccentricer extends Role {
  public $ability = 'ability_eccentricer';

  protected function IgnoreResult() {
    return $this->IsActive();
  }

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult($this->ability, null);
  }

  public function FilterVoteDo(&$count) {
    if ($this->IsActive()) $count++;
  }

  //能力発動判定
  private function IsActive() {
    return DB::$ROOM->date < 5;
  }
}
