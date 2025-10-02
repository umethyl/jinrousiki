<?php
/*
  ◆麒麟 (divine_escaper)
  ○仕様
  ・逃亡失敗：人狼系・暗殺者系・鬼陣営
  ・逃亡処理：一日村長 (村人陣営限定)
*/
RoleLoader::LoadFile('escaper');
class Role_divine_escaper extends Role_escaper {
  protected function EscapeFailed(User $user) {
    return $user->IsMainGroup(CampGroup::WOLF, CampGroup::ASSASSIN) ||
      $user->IsMainCamp(Camp::OGRE);
  }

  protected function EscapeAction(User $user) {
    if ($user->IsCamp(Camp::HUMAN)) $user->AddDoom(1, 'day_voter');
  }
}
