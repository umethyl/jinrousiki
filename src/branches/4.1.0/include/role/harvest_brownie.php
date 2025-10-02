<?php
/*
  ◆豊穣神 (harvest_brownie)
  ○仕様
  ・処刑得票：会心 (村人陣営) or 凍傷 (処刑)
*/
class Role_harvest_brownie extends Role {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillReaction() {
    foreach ($this->GetStackKey() as $uname) {
      $flag = $this->IsVoteKill($uname);
      foreach ($this->GetVotePollList($uname) as $target_uname) {
	$user = DB::$USER->ByRealUname($target_uname);
	if ($user->IsDead(true) || RoleUser::IsAvoid($user) || false === Lottery::Percent(30)) {
	  continue;
	}

	if (true === $flag) {
	  $user->AddDoom(1, 'frostbite');
	} elseif ($user->IsWinCamp(Camp::HUMAN)) {
	  $user->AddRole('critical_voter');
	}
      }
    }
  }
}
