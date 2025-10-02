<?php
/*
  ◆印狼 (ascetic_wolf)
  ○仕様
  ・投票数：+N (条件つき)
*/
RoleManager::LoadFile('wolf');
class Role_ascetic_wolf extends Role_wolf {
  protected function OutputResult() {
    RoleHTML::OutputAbilityResult('ability_ascetic_' . $this->GetAsceticCount(), null);
  }

  function FilterVoteDo(&$number) { $number += floor($this->GetAsceticCount() / 3); }

  //周囲の生存者判定
  private function GetAsceticCount() {
    $stack = $this->GetActor()->GetAround();
    $count = 1;
    foreach ($stack as $id) {
      if (! DB::$USER->IsVirtualLive($id)) $count++;
    }
    return $count;
  }
}
