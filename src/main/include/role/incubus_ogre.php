<?php
/*
  ◆般若 (incubus_ogre)
  ○仕様
  ・勝利：生存 + 女性全滅
  ・人攫い成功率低下：1/2
  ・人狼襲撃無効確率：40%
  ・暗殺反射確率：40%
*/
RoleLoader::LoadFile('ogre');
class Role_incubus_ogre extends Role_ogre {
  protected function GetOgreWolfEatResistRate() {
    return 40;
  }

  public function GetReflectAssassinRate() {
    return 40;
  }

  protected function GetOgreReduceDenominator() {
    return 2;
  }

  protected function IgnoreOgreLoseSurvive() {
    return false;
  }

  protected function RequireOgreWinDead(User $user) {
    return ! $this->IsActor($user) && Sex::IsFemale($user);
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }
}
