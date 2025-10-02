<?php
/*
  ◆祟神 (cursed_brownie)
  ○仕様
  ・処刑得票：死の宣告 (一定確率)
  ・人狼襲撃：死の宣告
*/
class Role_cursed_brownie extends Role {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillReaction() {
    foreach ($this->GetStackKey() as $uname) {
      foreach ($this->GetVotePollList($uname) as $target_uname) {
	$user = DB::$USER->ByRealUname($target_uname);
	if ($user->IsLive(true) && false === RoleUser::IsAvoid($user) && Lottery::Percent(30)) {
	  $user->AddDoom(2);
	}
      }
    }
  }

  public function WolfEatCounter(User $user) {
    $user->AddDoom(2);
  }
}
