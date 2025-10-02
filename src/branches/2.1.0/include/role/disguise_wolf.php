<?php
/*
  ◆朔狼 (disguise_wolf)
  ○仕様
 ・処刑投票：囁き狂人変化 (人狼陣営限定)
*/
RoleManager::LoadFile('wolf');
class Role_disguise_wolf extends Role_wolf {
  public $mix_in = 'critical_mad';

  function VoteAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      $target = DB::$USER->ByRealUname($target_uname);
      if ($target->IsDead(true) || ! $target->IsWolf()) continue;
      $user = DB::$USER->ByUname($uname);
      if ($user->IsLive(true)) {
	$user->ReplaceRole($user->main_role, 'whisper_mad');
	$user->AddRole('changed_disguise');
      }
    }
  }
}
