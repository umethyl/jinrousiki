<?php
/*
  ◆縁切地蔵 (divorce_jealousy)
  ○仕様
  ・処刑得票：告白付加 (恋人・一定確率)
*/
RoleLoader::LoadFile('jealousy');
class Role_divorce_jealousy extends Role_jealousy {
  public function VoteKillReaction() {
    foreach ($this->GetStackKey() as $uname) {
      if ($this->IsVoteKill($uname)) {
	continue;
      }

      foreach ($this->GetVotePollList($uname) as $target_uname) {
	$user = DB::$USER->ByRealUname($target_uname);
	if ($user->IsLiveRole('lovers', true) && Lottery::Percent(40)) {
	  $user->AddRole('confession');
	}
      }
    }
  }
}
