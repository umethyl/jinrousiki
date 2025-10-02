<?php
/*
  ◆傾奇者 (eccentricer)
  ○仕様
  ・投票数：+1 (4日目まで)
*/
class Role_eccentricer extends Role {
  public $mix_in = array('authority');
  public $ability = 'ability_eccentricer';

  protected function IgnoreResult() {
    return ! $this->IgnoreFilterVoteDo();
  }

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult($this->ability, null);
  }

  public function IgnoreFilterVoteDo() {
    return DB::$ROOM->date > 4;
  }
}
