<?php
/*
  ◆前鬼 (orange_ogre)
  ○仕様
  ・勝利：生存 + 人狼陣営全滅
*/
RoleLoader::LoadFile('ogre');
class Role_orange_ogre extends Role_ogre {
  protected function IsOgreLoseCamp($winner) {
    return $winner == WinCamp::WOLF;
  }

  protected function IgnoreOgreLoseSurvive() {
    return false;
  }

  protected function RequireOgreWinDead(User $user) {
    return $user->IsWinCamp(Camp::WOLF);
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }
}
