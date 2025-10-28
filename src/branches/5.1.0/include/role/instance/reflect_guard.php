<?php
/*
  ◆侍 (reflect_guard)
  ○仕様
  ・狩り：+ 鬼陣営
*/
RoleLoader::LoadFile('guard');
class Role_reflect_guard extends Role_guard {
  protected function IsAddHunt(User $user) {
    return $user->IsMainCamp(Camp::OGRE);
  }
}
