<?php
/*
  ◆紫狐 (purple_fox)
  ○仕様
  ・処刑投票：死の宣告獲得 (人狼陣営限定)
*/
RoleManager::LoadFile('fox');
class Role_purple_fox extends Role_fox {
  public $mix_in = 'critical_mad';

  function VoteAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      $target = DB::$USER->ByRealUname($target_uname);
      if ($target->IsDead(true) || ! $target->IsCamp('wolf', true)) continue;
      $user = DB::$USER->ByUname($uname);
      if ($user->IsLive(true)) $user->AddDoom(3);
    }
  }
}
