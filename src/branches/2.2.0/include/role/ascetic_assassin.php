<?php
/*
  ◆修験者 (ascetic_assassin)
  ○仕様
  ・人狼襲撃耐性：無効 (確率)
*/
RoleManager::LoadFile('assassin');
class Role_ascetic_assassin extends Role_assassin {
  protected function OutputResult() {
    RoleHTML::OutputAbilityResult('ability_ascetic_' . $this->GetAsceticCount(), null);
  }

  function WolfEatResist() { return Lottery::Percent(floor($this->GetAsceticCount() / 3) * 10); }

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
