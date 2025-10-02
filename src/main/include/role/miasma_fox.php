<?php
/*
  ◆蟲狐 (miasma_fox)
  ○仕様
  ・処刑：熱病 (妖狐陣営以外)
  ・人狼襲撃：熱病
*/
RoleManager::LoadFile('child_fox');
class Role_miasma_fox extends Role_child_fox {
  public $mix_in = null;
  public $action = null;
  public $result = null;

  function VoteKillCounter(array $list) {
    $stack = array();
    foreach ($list as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      if (! $user->IsAvoid() && ! $user->IsFox()) $stack[] = $user->id;
    }
    if (count($stack) > 0) DB::$USER->ByID(Lottery::Get($stack))->AddDoom(1, 'febris');
  }

  function WolfEatCounter(User $user) { $user->AddDoom(1, 'febris'); }
}
