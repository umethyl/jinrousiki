<?php
/*
  ◆鈴蘭人形 (poison_doll)
  ○仕様
  ・毒：人形以外
*/
RoleLoader::LoadFile('doll');
class Role_poison_doll extends Role_doll {
  public $mix_in = array('poison');

  protected function IsPoisonTarget(User $user) {
    return ! $this->IsDoll($user);
  }
}
