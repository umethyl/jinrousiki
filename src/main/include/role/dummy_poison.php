<?php
/*
  ◆夢毒者 (dummy_poison)
  ○仕様
  ・毒：昼限定 / 獏・妖精系
*/
RoleLoader::LoadFile('poison');
class Role_dummy_poison extends Role_poison {
  public $display_role = 'poison';

  public function IsPoison() {
    return DB::$ROOM->IsDay();
  }

  protected function IsPoisonTarget(User $user) {
    return RoleUser::IsDreamTarget($user);
  }
}
