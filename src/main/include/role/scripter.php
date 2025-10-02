<?php
/*
  ◆執筆者 (scripter)
  ○仕様
  ・投票数：+1 (5日目以降)
*/
class Role_scripter extends Role {
  public $mix_in = array('upper_voter');
  public $ability = 'ability_scripter';

  protected function IgnoreResult() {
    return $this->IgnoreFilterVoteDo();
  }

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult($this->ability, null);
  }
}
