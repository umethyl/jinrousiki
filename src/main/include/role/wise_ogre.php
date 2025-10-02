<?php
/*
  ◆夜行鬼 (wise_ogre)
  ○仕様
  ・勝利：生存 + 共有者系・人狼系・妖狐系全滅
  ・人攫い成功率低下：1/2
  ・人狼襲撃無効確率：40%
  ・暗殺反射確率：40%
*/
RoleLoader::LoadFile('ogre');
class Role_wise_ogre extends Role_ogre {
  public $mix_in = ['common'];

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
    return $user->IsMainGroup(CampGroup::COMMON, CampGroup::WOLF, CampGroup::FOX);
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }
}
