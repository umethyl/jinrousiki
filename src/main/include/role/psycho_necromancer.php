<?php
/*
  ◆精神感応者 (psycho_necromancer)
  ○仕様
  ・霊能：前世 (順番依存有り)
*/
RoleLoader::LoadFile('necromancer');
class Role_psycho_necromancer extends Role_necromancer {
  public $mix_in = ['psycho_mage'];
  public $result = RoleAbility::PSYCHO_NECROMANCER;

  public function Necromancer(User $user, $flag) {
    if (true === $flag) {
      return 'stolen';
    } elseif ($user->IsRoleGroup('copied')) {
      $result = 'mania';
    } elseif ($user->IsRole('changed_therian')) {
      $result = 'mad';
    } elseif ($user->IsRole('changed_vindictive')) {
      $result = 'child_fox';
    } elseif ($user->IsMainGroup(CampGroup::UNKNOWN_MANIA)) {
      $result = 'mania';
    } elseif ($user->IsMainGroup(CampGroup::DEPRAVER)) {
      $result = 'fox';
    } elseif ($user->IsMainGroup(CampGroup::MAD)) {
      $result = 'wolf';
    } elseif ($user->IsMainCamp(Camp::LOVERS)) {
      $result = 'lovers';
    } elseif ($this->IsLiar($user)) {
      $result = 'mad';
    } else {
      $result = 'human';
    }
    return Text::AddFooter($this->role, $result);
  }
}
