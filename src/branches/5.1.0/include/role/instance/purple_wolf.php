<?php
/*
  ◆紫狼 (purple_wolf)
  ○仕様
  ・処刑投票：死の宣告獲得 (妖狐陣営限定)
*/
RoleLoader::LoadFile('wolf');
class Role_purple_wolf extends Role_wolf {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoteKill($uname)) {
	continue;
      }

      $target = DB::$USER->ByRealUname($target_uname);
      if ($target->IsDead(true) || false === $target->IsWinCamp(Camp::FOX)) {
	continue;
      }

      $user = DB::$USER->ByUname($uname);
      if ($user->IsLive(true)) {
	$user->AddDoom(3);
      }
    }
  }
}
