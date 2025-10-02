<?php
/*
  ◆紅天使 (scarlet_angel)
  ○仕様
  ・仲間表示：＋無意識枠
  ・共感者判定：常時有効
*/
RoleManager::LoadFile('angel');
class Role_scarlet_angel extends Role_angel {
  protected function OutputPartner() {
    parent::OutputPartner();
    if (! DB::$ROOM->IsNight()) return;
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      if ($this->IsActor($user) || $user->IsWolf()) continue;
      if ($user->IsRole('unconscious') || $user->IsRoleGroup('scarlet')) {
	$stack[] = $user->handle_name;
      }
    }
    RoleHTML::OutputPartner($stack, 'unconscious_list');
  }

  protected function IsSympathy(User $a, User $b) { return true; }
}
