<?php
/*
  ◆傾奇者 (eccentricer)
  ○仕様
  ・能力結果：能力喪失
  ・投票数：+1 (4 日目まで)
*/
class Role_eccentricer extends Role {
  public $mix_in = ['authority'];

  protected function IgnoreResult() {
    return ! $this->IgnoreFilterVoteDo();
  }

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult('ability_eccentricer', null);
  }

  protected function IgnoreFilterVoteDo() {
    return DB::$ROOM->date > 4;
  }
}
