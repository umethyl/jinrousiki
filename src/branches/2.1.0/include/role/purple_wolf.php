<?php
/*
  ◆紫狼 (purple_wolf)
  ○仕様
  ・処刑投票：死の宣告獲得 (妖狐陣営限定)
*/
RoleManager::LoadFile('wolf');
class Role_purple_wolf extends Role_wolf {
  public $mix_in = 'critical_mad';

  function VoteAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      $target = DB::$USER->ByRealUname($target_uname);
      if ($target->IsDead(true) || ! $target->IsCamp('fox', true)) continue;
      $user = DB::$USER->ByUname($uname);
      if ($user->IsLive(true)) $user->AddDoom(3);
    }
  }
}
