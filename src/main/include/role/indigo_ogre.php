<?php
/*
  ◆後鬼 (indigo_ogre)
  ○仕様
  ・勝利：生存 + 妖狐陣営全滅
*/
RoleLoader::LoadFile('ogre');
class Role_indigo_ogre extends Role_ogre {
  protected function IsOgreLoseCamp($winner) {
    return $winner == WinCamp::FOX;
  }

  protected function IgnoreOgreLoseSurvive() {
    return false;
  }

  protected function RequireOgreWinDead(User $user) {
    return $user->IsWinCamp(Camp::FOX);
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }
}
