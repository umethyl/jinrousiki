<?php
/*
  ◆夢毒者 (dummy_poison)
  ○仕様
  ・毒：昼限定 / 獏・妖精系
*/
RoleManager::LoadFile('poison');
class Role_dummy_poison extends Role_poison {
  public $display_role = 'poison';

  function IsPoison() { return DB::$ROOM->IsDay(); }

  function IsPoisonTarget(User $user) {
    return $user->IsRole('dream_eater_mad') || $user->IsRoleGroup('fairy');
  }
}
