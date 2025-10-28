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
      if ($this->IsVoteKill($uname)) {
	continue;
      }

      $user = DB::$USER->ByRealUname($uname);
      if (RoleUser::AvoidLovers($user, true)) {
	continue;
      }

      foreach ($this->GetVotePollList($uname) as $target_uname) {
	if (DB::$USER->ByRealUname($target_uname)->IsMainGroup(CampGroup::MAD)) {
	  $user->AddDoom(1, 'frostbite');
	  break;
	}
      }
    }
  }
}
