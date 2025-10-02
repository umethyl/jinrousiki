<?php
/*
  ◆紅葉神 (maple_brownie)
  ○仕様
  ・処刑得票：痛恨 (村人陣営) + 凍傷 (処刑)
*/
class Role_maple_brownie extends Role {
  public $vote_day_type = 'init';

  public function VoteKillReaction() {
    foreach (array_keys($this->GetStack()) as $uname) {
      $flag = $this->IsVoted($uname);
      foreach ($this->GetVotedUname($uname) as $voted_uname) {
	$user = DB::$USER->ByRealUname($voted_uname);
	if ($user->IsDead(true) || $user->IsAvoid()) continue;

	if ($user->IsCamp('human', true) && Lottery::Percent(30)) {
	  $user->AddRole('critical_luck');
	}
	if ($flag && Lottery::Percent(30)) $user->AddDoom(1, 'frostbite');
      }
    }
  }
}
