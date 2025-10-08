<?php
/*
  ◆門番 (gatekeeper_guard)
  ○仕様
  ・狩り：なし
*/
RoleLoader::LoadFile('guard');
class Role_gatekeeper_guard extends Role_guard {
  protected function SetGuardAction(User $user) {
    $this->AddStack($user->id);
  }

  public function IgnoreHunt() {
    return true;
  }

  //対暗殺護衛
  public function GuardAssassin($id) {
    $stack = $this->GetStackKey($this->role, $id); //護衛判定
    if (count($stack) < 1) {
      return false;
    }

    //護衛成功者を検出
    $guard_stack = [];
    foreach ($stack as $guard_id) {
      $user = DB::$USER->ByID($guard_id);
      if ($user->IsLive(true)) {
	$guard_stack[] = $user;
      }
    }
    if (count($guard_stack) < 1) {
      return false;
    }

    //護衛成功メッセージを登録
    $handle_name = DB::$USER->ByVirtual($id)->handle_name;
    foreach ($guard_stack as $user) {
      if (RoleUser::GuardSuccess($user, $id)) {
	DB::$ROOM->StoreAbility($this->result, 'success', $handle_name, $user->id);
      }
    }
    return true;
  }
}
