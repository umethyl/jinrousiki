<?php
/*
  ◆座敷童子 (brownie)
  ○仕様
  ・処刑：熱病
*/
class Role_brownie extends Role {
  public function VoteKillCounter(array $list) {
    $stack = [];
    foreach ($list as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      if (! RoleUser::IsAvoid($user)) {
	$stack[] = $user->id;
      }
    }

    if (count($stack) > 0) {
      DB::$USER->ByID(Lottery::Get($stack))->AddDoom(1, 'febris');
    }
  }
}
