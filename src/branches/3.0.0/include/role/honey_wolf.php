<?php
/*
  ◆蜜狼 (honey_wolf)
  ○仕様
  ・妖狐襲撃：自決
  ・襲撃：自決
  ・自決：恋人・LWは除く
*/
RoleManager::LoadFile('wolf');
class Role_honey_wolf extends Role_wolf {
  protected function FoxEatAction(User $user) {
    $this->Suicide();
  }

  public function WolfEatAction(User $user) {
    $this->Suicide();
    return false;
  }

  //自決処理
  private function Suicide() {
    $actor = $this->GetWolfVoter();
    if ($actor->IsLovers() || count(DB::$USER->GetLivingWolves()) < 2) return;
    DB::$USER->Kill($actor->id, 'SUICIDE');
  }
}
