<?php
/*
  ◆人魚 (critical_jealousy)
  ○仕様
  ・処刑投票：痛恨獲得 (恋人限定)
*/
RoleLoader::LoadFile('jealousy');
class Role_critical_jealousy extends Role_jealousy {
  public function VoteKillAction() {
    $role = 'critical_luck';
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;

      $user = DB::$USER->ByUname($uname);
      if ($user->IsDead(true) || $user->IsRole($role)) continue;

      $target = DB::$USER->ByRealUname($target_uname);
      if ($target->IsLiveRole('lovers', true)) $user->AddRole($role);
    }
  }
}
