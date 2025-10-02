<?php
/*
  ◆馬頭鬼 (horse_ogre)
  ○仕様
  ・勝利：生存 + 毒能力者全滅 + 村人陣営の勝利
*/
RoleLoader::LoadFile('ogre');
class Role_horse_ogre extends Role_ogre {
  protected function IsOgreLoseCamp($winner) {
    return $winner != WinCamp::HUMAN;
  }

  protected function IgnoreOgreLoseSurvive() {
    return false;
  }

  protected function RequireOgreWinDead(User $user) {
    return $user->IsRoleGroup('poison');
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }
}
