<?php
/*
  ◆雪狼 (snow_wolf)
  ○仕様
  ・処刑得票：凍傷 (子狐系限定)
*/
RoleManager::LoadFile('wolf');
class Role_snow_wolf extends Role_wolf {
  function SetVoteDay($uname) {
    $this->InitStack();
    if ($this->IsRealActor()) $this->AddStackName($uname);
  }

  function VoteKillReaction() {
    foreach (array_keys($this->GetStack()) as $uname) {
      if ($this->IsVoted($uname)) continue;
      foreach ($this->GetVotedUname($uname) as $voted_uname) {
	if (DB::$USER->ByRealUname($voted_uname)->IsChildFox()) {
	  DB::$USER->ByRealUname($uname)->AddDoom(1, 'frostbite');
	}
      }
    }
  }
}
