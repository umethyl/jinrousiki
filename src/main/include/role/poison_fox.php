<?php
/*
  ◆管狐 (poison_fox)
  ○仕様
  ・人狼襲撃耐性：無し
  ・毒：妖狐カウント以外
*/
RoleLoader::LoadFile('fox');
class Role_poison_fox extends Role_fox {
  public $mix_in = ['poison'];

  protected function IsPoisonTarget(User $user) {
    return false === RoleUser::IsFoxCount($user);
  }

  public function ResistWolfEatFox() {
    return false;
  }
}
