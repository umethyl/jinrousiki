<?php
/*
  ◆門番 (gatekeeper_guard)
  ○仕様
  ・狩り：なし
*/
RoleManager::LoadFile('guard');
class Role_gatekeeper_guard extends Role_guard {
  function SetGuard(User $user) {
    if (! parent::SetGuard($user)) return false;
    $this->AddStack($user->id);
    return true;
  }

  protected function IsHunt(User $user) { return false; }

  //対暗殺護衛
  final function GuardAssassin($id) {
    $stack = array_keys($this->GetStack(), $id); //護衛判定
    if (count($stack) < 1) return false;

    //護衛成功者を検出
    $guard_stack = array();
    foreach ($stack as $guard_id) {
      $user = DB::$USER->ByID($guard_id);
      if ($user->IsLive(true)) $guard_stack[] = $user;
    }
    if (count($guard_stack) < 1) return false;

    //護衛成功メッセージを登録
    if (DB::$ROOM->IsOption('seal_message')) return true;
    $handle_name = DB::$USER->ByVirtual($id)->handle_name;
    foreach ($guard_stack as $user) {
      if ($user->IsFirstGuardSuccess($id)) {
	DB::$ROOM->ResultAbility('GUARD_SUCCESS', 'success', $handle_name, $user->id);
      }
    }
    return true;
  }
}
