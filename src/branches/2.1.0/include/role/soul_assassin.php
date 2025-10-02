<?php
/*
  ◆辻斬り (soul_assassin)
  ○仕様
  ・暗殺：役職判定 + 毒死(毒能力者)
*/
RoleManager::LoadFile('assassin');
class Role_soul_assassin extends Role_assassin {
  public $result = 'ASSASSIN_RESULT';

  protected function OutputResult() {
    if (DB::$ROOM->date > 2) $this->OutputAbilityResult($this->result);
  }

  function Assassin(User $user) {
    if (! parent::Assassin($user)) return false;
    $id = $this->GetID();
    $target = DB::$USER->GetHandleName($user->uname, true);
    DB::$ROOM->ResultAbility($this->result, $user->main_role, $target, $id);

    if ($user->IsPoison()) DB::$USER->Kill($id, 'POISON_DEAD'); //毒死判定
  }
}
