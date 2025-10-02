<?php
/*
  ◆狂信者 (fanatic_mad)
  ○仕様
*/
class Role_fanatic_mad extends Role {
  protected function OutputPartner() {
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsRole('possessed_wolf')) {
	$stack[] = DB::$USER->GetHandleName($user->uname, true); //憑依先を追跡する
      }
      elseif ($user->IsWolf(true)) {
	$stack[] = $user->handle_name;
      }
    }
    RoleHTML::OutputPartner($stack, 'wolf_partner');
  }
}
