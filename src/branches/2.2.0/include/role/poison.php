<?php
/*
  ◆埋毒者 (poison)
  ○仕様
  ・毒：常時 / 制限なし
*/
class Role_poison extends Role {
  //毒対象者選出 (処刑)
  function GetPoisonVoteTarget(array $list) {
    $stack     = array();
    $aspirator = array();
    $class = $this->GetClass($method = 'IsPoisonTarget');
    foreach ($list as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      if ($user->IsLive(true) && ! $user->IsAvoidPoison(true) && $class->$method($user)) {
	if ($user->IsRole('aspirator')) { //吸毒者判定
	  $aspirator[] = $user->id;
	} else {
	  $stack[] = $user->id;
	}
      }
    }
    return count($aspirator) > 0 ? $aspirator : $stack;
  }

  //毒発動判定
  function IsPoison() { return true; }

  //毒対象者判定
  function IsPoisonTarget(User $user) { return true; }
}
