<?php
/*
  ◆潜毒者 (incubate_poison)
  ○仕様
  ・毒：5日目以降 / 人外カウント
*/
RoleManager::LoadFile('poison');
class Role_incubate_poison extends Role_poison {
  public $ability = 'ability_poison';

  function OutputResult() {
    if ($this->IsPoison()) RoleHTML::OutputAbilityResult($this->ability, null);
  }

  function IsPoison() { return DB::$ROOM->date > 4; }

  function IsPoisonTarget(User $user) { return $user->IsInhuman(); }
}
