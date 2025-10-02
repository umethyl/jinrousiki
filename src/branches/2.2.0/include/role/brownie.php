<?php
/*
  ◆座敷童子 (brownie)
  ○仕様
  ・処刑：熱病
*/
class Role_brownie extends Role {
  function VoteKillCounter(array $list) {
    $stack = array();
    foreach ($list as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      if (! $user->IsAvoid()) $stack[] = $user->id;
    }
    if (count($stack) > 0) DB::$USER->ByID(Lottery::Get($stack))->AddDoom(1, 'febris');
  }
}
