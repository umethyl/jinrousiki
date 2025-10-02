<?php
/*
  ◆夜叉 (yaksa)
  ○仕様
  ・勝利：生存 + 人狼系全滅
  ・人狼襲撃無効確率：20%
  ・暗殺反射確率：20%
  ・人攫い無効：人狼系以外
*/
RoleLoader::LoadFile('ogre');
class Role_yaksa extends Role_ogre {
  protected function GetOgreResistWolfEatRate() {
    return 20;
  }

  public function GetReflectAssassinRate() {
    return 20;
  }

  protected function IgnoreOgreAssassin(User $user) {
    return false === $this->RequireOgreWinDead($user);
  }

  protected function IsOgreLoseCamp($winner) {
    return $winner == WinCamp::WOLF;
  }

  protected function IgnoreOgreLoseSurvive() {
    return false;
  }

  protected function RequireOgreWinDead(User $user) {
    return $user->IsMainGroup(CampGroup::WOLF);
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }
}
