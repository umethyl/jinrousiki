<?php
/*
  ◆餓狼 (hungry_wolf)
  ○仕様
  ・襲撃：人狼系・妖狐陣営 (人外カウント) 以外無効
*/
RoleManager::LoadFile('wolf');
class Role_hungry_wolf extends Role_wolf {
  protected function IsWolfEatTarget($id) {
    return true;
  }

  public function WolfEatSkip(User $user) {
    return false;
  }

  public function WolfEatAction(User $user) {
    return ! $user->IsInhuman();
  }

  public function WolfKill(User $user) {
    DB::$USER->Kill($user->id, 'HUNGRY_WOLF_KILLED');
  }
}
