<?php
/*
  ◆強毒者 (strong_poison)
  ○仕様
  ・毒：人外カウント
*/
RoleLoader::LoadFile('poison');
class Role_strong_poison extends Role_poison {
  public $display_role = 'poison';

  protected function IsPoisonTarget(User $user) {
    return RoleUser::IsInhuman($user);
  }
}
