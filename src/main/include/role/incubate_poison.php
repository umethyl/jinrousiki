<?php
/*
  ◆潜毒者 (incubate_poison)
  ○仕様
  ・毒：5日目以降 / 人狼系 + 妖狐陣営
*/
RoleManager::LoadFile('poison');
class Role_incubate_poison extends Role_poison {
  public $ability = 'muster_ability';

  function OutputResult() {
    if (DB::$ROOM->date > 4) RoleHTML::OutputAbilityResult($this->ability, null);
  }

  function IsPoison() { return DB::$ROOM->date >= 5; }

  function IsPoisonTarget(User $user) { return $user->IsRoleGroup('wolf', 'fox'); }
}
