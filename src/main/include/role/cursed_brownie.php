<?php
/*
  ◆祟神 (cursed_brownie)
  ○仕様
  ・処刑得票：死の宣告 (一定確率)
  ・人狼襲撃：死の宣告
*/
class Role_cursed_brownie extends Role {
  public $vote_day_type = 'init';

  public function VoteKillReaction() {
    foreach (array_keys($this->GetStack()) as $uname) {
      foreach ($this->GetVotedUname($uname) as $voted_uname) {
	$user = DB::$USER->ByRealUname($voted_uname);
	if ($user->IsLive(true) && ! $user->IsAvoid() && Lottery::Percent(30)) $user->AddDoom(2);
      }
    }
  }

  public function WolfEatCounter(User $user) {
    $user->AddDoom(2);
  }
}
