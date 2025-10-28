<?php
/*
  ◆爵狼 (homogeneous_wolf)
  ○仕様
  ・処刑得票：異性なら死の宣告 (70%)
*/
RoleLoader::LoadFile('wolf');
class Role_homogeneous_wolf extends Role_wolf {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillReaction() {
    foreach ($this->GetStackKey() as $uname) {
      if ($this->IsVoteKill($uname)) {
	continue;
      }

      $user = DB::$USER->ByRealUname($uname);
      foreach ($this->GetVotePollList($uname) as $target_uname) {
	$target = DB::$USER->ByRealUname($target_uname);
	if (true === $this->IsVoteKillReaction($user, $target)) {
	  $target->AddDoom(4);
	}
      }
    }
  }

  //処刑得票カウンター発動判定 (回避対象 > 確率 > 性別)
  protected function IsVotekillReaction(User $user, User $target) {
    if (true === RoleUser::Avoid($target)) {
      return false;
    }

    if (false === Lottery::Percent(70)) {
      return false;
    }

    return $this->IsVotekillReactionSex() === Sex::IsSame($user, $target);
  }

  //処刑得票カウンター発動対象性別判定
  protected function IsVotekillReactionSex() {
    return false;
  }
}
