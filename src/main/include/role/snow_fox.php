<?php
/*
  ◆雪狐 (snow_fox)
  ○仕様
  ・処刑得票：凍傷 (投票者：狂人系限定)
*/
RoleManager::LoadFile('fox');
class Role_snow_fox extends Role_fox {
  public $vote_day_type = 'init';

  public function VoteKillReaction() {
    foreach (array_keys($this->GetStack()) as $uname) {
      if ($this->IsVoted($uname)) continue;

      $user = DB::$USER->ByRealUname($uname);
      if ($user->IsAvoidLovers(true)) continue;

      foreach ($this->GetVotedUname($uname) as $voted_uname) {
	if (DB::$USER->ByRealUname($voted_uname)->IsMainGroup('mad')) {
	  $user->AddDoom(1, 'frostbite');
	  break;
	}
      }
    }
  }
}
