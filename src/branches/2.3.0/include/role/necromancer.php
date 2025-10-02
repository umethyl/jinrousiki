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
    if ($user->IsMainCamp('ogre')) return 'ogre';
    if ($user->IsMainGroup('vampire') || $user->IsRole('cute_chiroptera')) return 'chiroptera';
    if ($user->IsChildFox()) return 'child_fox';
    if ($user->IsRole('white_fox', 'black_fox', 'mist_fox', 'phantom_fox', 'sacrifice_fox',
		      'possessed_fox', 'cursed_fox')) {
      return 'fox';
    }
    if ($user->IsRole('boss_wolf', 'mist_wolf', 'phantom_wolf', 'cursed_wolf', 'possessed_wolf')) {
      return $user->main_role;
    }
    return ($user->IsWolf() xor $reverse) ? 'wolf' : 'human';
  }
}
