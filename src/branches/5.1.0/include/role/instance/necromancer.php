<?php
/*
  ◆霊能者 (necromancer)
  ○仕様
  ・能力結果：霊能
  ・霊能：通常
*/
class Role_necromancer extends Role {
  public $result = RoleAbility::NECROMANCER;

  protected function IgnoreResult() {
    return DateBorder::PreThree();
  }

  //霊能
  public function Necromancer(User $user, $flag) {
    return (true === $flag) ? 'stolen' : $this->DistinguishNecromancer($user);
  }

  //霊能判定
  final protected function DistinguishNecromancer(User $user, $reverse = false) {
    $camp = $user->DistinguishCamp();
    switch ($camp) {
    case Camp::WOLF:
      //特殊人狼
      $stack = [
        'boss_wolf', 'mist_wolf', 'tiger_wolf', 'phantom_wolf', 'cursed_wolf', 'spell_wolf',
	'possessed_wolf'
      ];
      if ($user->IsRole($stack)) {
	return 'wolf_special';
      }
      break;

    case Camp::FOX:
      //子狐
      if ($user->IsMainGroup(CampGroup::CHILD_FOX)) {
	return 'child_fox';
      }

      //特殊妖狐 (変化型も含む)
      $stack = [
        'white_fox', 'black_fox', 'mist_fox', 'tiger_fox', 'phantom_fox', 'sacrifice_fox',
	'possessed_fox', 'cursed_fox', 'changed_vindictive', 'changed_tailtip'
      ];
      if ($user->IsRole($stack)) {
	return $camp;
      }
      break;

    case Camp::VAMPIRE:
      return 'chiroptera';

    case Camp::CHIROPTERA:
      if ($user->IsRole('cute_chiroptera')) {
	return $camp;
      }
      break;

    case Camp::OGRE:
    case Camp::TENGU:
      return $camp;
    }

    return ($user->IsMainGroup(CampGroup::WOLF) xor (true === $reverse)) ? 'wolf' : 'human';
  }
}
