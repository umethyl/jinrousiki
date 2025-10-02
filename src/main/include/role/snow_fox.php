<?php
/*
  ◆雪狐 (snow_fox)
  ○仕様
  ・処刑得票：凍傷 (投票者：狂人系限定)
*/
RoleLoader::LoadFile('fox');
class Role_snow_fox extends Role_fox {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillReaction() {
    foreach ($this->GetStackKey() as $uname) {
      if ($this->IsVoted($uname)) continue;

      $user = DB::$USER->ByRealUname($uname);
      if (RoleUser::IsAvoidLovers($user, true)) continue;

      foreach ($this->GetVotedUname($uname) as $voted_uname) {
	if (DB::$USER->ByRealUname($voted_uname)->IsMainGroup(CampGroup::MAD)) {
	  $user->AddDoom(1, 'frostbite');
	  break;
	}
      }
    }
  }
}
