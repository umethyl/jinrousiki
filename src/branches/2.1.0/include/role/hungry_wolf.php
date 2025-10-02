<?php
/*
  ◆餓狼 (hungry_wolf)
  ○仕様
  ・襲撃：人狼系・妖狐陣営以外無効
*/
RoleManager::LoadFile('wolf');
class Role_hungry_wolf extends Role_wolf {
  function IsWolfEatTarget($id) { return true; }

  function WolfEatSkip(User $user) { return false; }

  function WolfEatAction(User $user) { return ! $user->IsRoleGroup('wolf', 'fox'); }

  function WolfKill(User $user) { DB::$USER->Kill($user->user_no, 'HUNGRY_WOLF_KILLED'); }
}
