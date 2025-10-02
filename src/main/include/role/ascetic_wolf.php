<?php
/*
  ◆印狼 (ascetic_wolf)
  ○仕様
  ・投票数：+N (条件つき)
*/
RoleManager::LoadFile('wolf');
class Role_ascetic_wolf extends Role_wolf {
  public $mix_in = array('authority');

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult('ability_ascetic_' . $this->GetAsceticCount(), null);
  }

  public function GetVoteDoCount() {
    return floor($this->GetAsceticCount() / 3);
  }

  //周囲の生存者判定
  private function GetAsceticCount() {
    $count = 1;
    foreach ($this->GetActor()->GetAround() as $id) {
      if (! DB::$USER->IsVirtualLive($id)) $count++;
    }
    return $count;
  }
}
