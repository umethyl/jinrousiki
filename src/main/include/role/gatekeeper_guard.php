<?php
/*
  ◆門番 (gatekeeper_guard)
  ○仕様
  ・狩り：なし
*/
RoleManager::LoadFile('guard');
class Role_gatekeeper_guard extends Role_guard {
  function SetGuard($uname) {
    if (! parent::SetGuard($uname)) return false;
    $this->AddStack($uname);
    return true;
  }

  protected function IsHunt(User $user) { return false; }

  //対暗殺護衛
  function GuardAssassin($uname) {
    $stack = array_keys($this->GetStack(), $uname); //護衛判定
    if (count($stack) < 1) return false;

    //護衛成功者を検出
    $guard_stack = array();
    foreach ($stack as $guard_uname) {
      $user = DB::$USER->ByUname($guard_uname);
      if ($user->IsLive(true)) $guard_stack[] = $user;
    }
    if (count($guard_stack) < 1) return false;

    //護衛成功メッセージを登録
    if (DB::$ROOM->IsOption('seal_message')) return true;
    $target = DB::$USER->GetHandleName($uname, true);
    foreach ($guard_stack as $user) {
      if ($user->IsFirstGuardSuccess($uname)) {
	DB::$ROOM->ResultAbility('GUARD_SUCCESS', 'success', $target, $user->user_no);
      }
    }
    return true;
  }
}
