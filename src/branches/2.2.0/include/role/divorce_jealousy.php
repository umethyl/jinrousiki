<?php
/*
  ◆縁切地蔵 (divorce_jealousy)
  ○仕様
  ・処刑得票：告白付加 (恋人・一定確率)
*/
RoleManager::LoadFile('jealousy');
class Role_divorce_jealousy extends Role_jealousy {
  function VoteKillReaction() {
    foreach (array_keys($this->GetStack()) as $uname) {
      if ($this->IsVoted($uname)) continue;
      foreach ($this->GetVotedUname($uname) as $voted_uname) {
	$user = DB::$USER->ByRealUname($voted_uname);
	if ($user->IsLive(true) && $user->IsLovers() && Lottery::Percent(40)) {
	  $user->AddRole('confession');
	}
      }
    }
  }
}
