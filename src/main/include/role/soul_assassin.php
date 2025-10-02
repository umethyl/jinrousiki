<?php
/*
  ◆辻斬り (soul_assassin)
  ○仕様
  ・暗殺：役職判定 / 毒死(毒能力者)
*/
RoleManager::LoadFile('assassin');
class Role_soul_assassin extends Role_assassin {
  public $result = 'ASSASSIN_RESULT';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  protected function AssassinAction(User $user) {
    if ($user->IsPoison()) {
      DB::$USER->Kill($this->GetID(), 'POISON_DEAD');
    } else {
      DB::$ROOM->ResultAbility($this->result, $user->main_role, $user->GetName(), $this->GetID());
    }
  }
}
