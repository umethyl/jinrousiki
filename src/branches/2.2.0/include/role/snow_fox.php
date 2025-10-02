<?php
/*
  ◆雪狐 (snow_fox)
  ○仕様
  ・処刑得票：凍傷 (投票者：狂人系限定)
*/
RoleManager::LoadFile('fox');
class Role_snow_fox extends Role_fox {
  function SetVoteDay($uname) {
    $this->InitStack();
    if ($this->IsRealActor()) $this->AddStackName($uname);
  }

  function VoteKillReaction() {
    foreach (array_keys($this->GetStack()) as $uname) {
      if ($this->IsVoted($uname)) continue;
      foreach ($this->GetVotedUname($uname) as $voted_uname) {
	if (DB::$USER->ByRealUname($voted_uname)->IsMainGroup('mad')) {
	  DB::$USER->ByRealUname($uname)->AddDoom(1, 'frostbite');
	}
      }
    }
  }
}
