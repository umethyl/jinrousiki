<?php
/*
  ◆雪狼 (snow_wolf)
  ○仕様
  ・処刑得票：凍傷 (子狐系限定)
*/
RoleLoader::LoadFile('wolf');
class Role_snow_wolf extends Role_wolf {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillReaction() {
    foreach ($this->GetStackKey() as $uname) {
      if ($this->IsVoteKill($uname)) {
	continue;
      }

      $user = DB::$USER->ByRealUname($uname);
      if (RoleUser::IsAvoidLovers($user, true)) {
	continue;
      }

      foreach ($this->GetVotePollList($uname) as $target_uname) {
	if (DB::$USER->ByRealUname($target_uname)->IsMainGroup(CampGroup::CHILD_FOX)) {
	  $user->AddDoom(1, 'frostbite');
	  break;
	}
      }
    }
  }
}
