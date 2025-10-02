<?php
/*
  ◆紫狐 (purple_fox)
  ○仕様
  ・処刑投票：死の宣告獲得 (人狼陣営限定)
*/
RoleLoader::LoadFile('fox');
class Role_purple_fox extends Role_fox {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;

      $target = DB::$USER->ByRealUname($target_uname);
      if ($target->IsDead(true) || ! $target->IsWinCamp(Camp::WOLF)) continue;

      $user = DB::$USER->ByUname($uname);
      if ($user->IsLive(true)) {
	$user->AddDoom(3);
      }
    }
  }
}
