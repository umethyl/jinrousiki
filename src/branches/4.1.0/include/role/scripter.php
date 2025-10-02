<?php
/*
  ◆執筆者 (scripter)
  ○仕様
  ・能力結果：発動発現
  ・投票数：+1 (5 日目以降)
*/
class Role_scripter extends Role {
  public $mix_in = ['upper_voter'];

  protected function IgnoreResult() {
    return $this->IgnoreFilterVoteDo();
  }

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult('ability_scripter', null);
  }
}
