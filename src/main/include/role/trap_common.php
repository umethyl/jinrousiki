<?php
/*
  ◆策士 (trap_common)
  ○仕様
  ・処刑得票カウンター：罠死 (非村人陣営の人全てからの得票)
*/
RoleLoader::LoadFile('common');
class Role_trap_common extends Role_common {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::ADD;
  }

  public function VotePollReaction() {
    $stack = $this->GetStack();
    if (false === is_array($stack) || count($stack) < 1) {
      return;
    }

    $target_list = [];
    //非村人陣営の ID と仮想ユーザ名を収集
    foreach ($this->GetStackKey(VoteDayElement::TARGET_LIST) as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      if (false === $user->IsWinCamp(Camp::HUMAN)) {
	$target_list[$user->id] = $user->GetVirtual()->uname;
      }
    }
    //Text::p($target_list, "◆InHuman [{$this->role}]");

    foreach (array_keys($stack) as $uname) { //策士の得票リストと照合
      if ($this->GetVotePollList($uname) == array_values($target_list)) {
	foreach (array_keys($target_list) as $id) {
	  DB::$USER->Kill($id, DeadReason::TRAPPED);
	}
      }
    }
  }
}
