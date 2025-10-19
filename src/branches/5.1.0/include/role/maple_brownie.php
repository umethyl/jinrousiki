<?php
/*
  ◆紅葉神 (maple_brownie)
  ○仕様
  ・処刑得票：痛恨 (村人陣営) + 凍傷 (処刑)
*/
class Role_maple_brownie extends Role {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillReaction() {
    foreach ($this->GetStackKey() as $uname) {
      $flag = $this->IsVoteKill($uname);
      foreach ($this->GetVotePollList($uname) as $target_uname) {
	$user = DB::$USER->ByRealUname($target_uname);
	if ($user->IsDead(true) || RoleUser::Avoid($user)) {
	  continue;
	}

	if ($user->IsWinCamp(Camp::HUMAN) && Lottery::Percent(30)) {
	  $user->AddRole('critical_luck');
	}

	if (true === $flag && Lottery::Percent(30)) {
	  $user->AddDoom(1, 'frostbite');
	}
      }
    }
  }
}
