<?php
/*
  ◆牛頭鬼 (cow_ogre)
  ○仕様
  ・勝利：生存 + 暗殺者系全滅 + 村人陣営の勝利
  ・暗殺反射確率：45%
*/
RoleLoader::LoadFile('ogre');
class Role_cow_ogre extends Role_ogre {
  public function GetReflectAssassinRate() {
    return 45;
  }

  protected function IsOgreLoseCamp($winner) {
    return $winner != WinCamp::HUMAN;
  }

  protected function IgnoreOgreLoseSurvive() {
    return false;
  }

  protected function RequireOgreWinDead(User $user) {
    return $user->IsMainGroup(CampGroup::ASSASSIN);
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }
}
