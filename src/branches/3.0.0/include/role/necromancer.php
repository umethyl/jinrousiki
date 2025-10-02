<?php
/*
  ◆霊能者 (necromancer)
  ○仕様
  ・霊能：通常
*/
class Role_necromancer extends Role {
  public $result = 'NECROMANCER_RESULT';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  //霊能
  public function Necromancer(User $user, $flag) {
    return $flag ? 'stolen' : $this->DistinguishNecromancer($user);
  }

  //霊能判定
  final public function DistinguishNecromancer(User $user, $reverse = false) {
    switch ($camp = $user->DistinguishCamp()) {
    case 'wolf':
      $stack = array('boss_wolf', 'mist_wolf', 'tiger_wolf', 'phantom_wolf',
		     'cursed_wolf', 'possessed_wolf');
      if ($user->IsRole($stack)) return $user->main_role;
      break;

    case 'fox':
      if ($user->IsChildFox()) return 'child_fox';

      $stack = array('white_fox', 'black_fox', 'mist_fox', 'tiger_fox', 'phantom_fox',
		     'sacrifice_fox', 'possessed_fox', 'cursed_fox');
      if ($user->IsRole($stack)) return $camp;
      break;

    case 'vampire':
      return 'chiroptera';

    case 'chiroptera':
      if ($user->IsRole('cute_chiroptera')) return $camp;
      break;

    case 'ogre':
    case 'tengu':
      return $camp;
    }

    return ($user->IsWolf() xor $reverse) ? 'wolf' : 'human';
  }
}
