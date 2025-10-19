<?php
/*
  ◆蟲狐 (miasma_fox)
  ○仕様
  ・処刑：熱病 (妖狐カウント以外)
  ・人狼襲撃：熱病
*/
RoleLoader::LoadFile('child_fox');
class Role_miasma_fox extends Role_child_fox {
  public $mix_in = null;
  public $action = null;
  public $result = null;

  public function VoteKillCounter(array $list) {
    $stack = [];
    foreach ($list as $uname) {
      $user = DB::$USER->ByRealUname($uname);
      if (false === RoleUser::IsFoxCount($user) && false === RoleUser::Avoid($user)) {
	$stack[] = $user->id;
      }
    }

    if (count($stack) > 0) {
      DB::$USER->ByID(Lottery::Get($stack))->AddDoom(1, 'febris');
    }
  }

  public function WolfEatCounter(User $user) {
    $user->AddDoom(1, 'febris');
  }
}
