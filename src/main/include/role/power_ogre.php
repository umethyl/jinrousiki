<?php
/*
  ◆星熊童子 (power_ogre)
  ○仕様
  ・勝利：生存 + 人口を三分の一以下にする
  ・人攫い成功率低下：7/10
  ・人狼襲撃無効確率：40%
  ・暗殺反射確率：40%
*/
RoleLoader::LoadFile('ogre');
class Role_power_ogre extends Role_ogre {
  protected function GetOgreResistWolfEatRate() {
    return 40;
  }

  public function GetReflectAssassinRate() {
    return 40;
  }

  protected function GetOgreReduceNumerator() {
    return 7;
  }

  protected function GetOgreReduceDenominator() {
    return 10;
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }

  protected function OgreWin() {
    return DB::$USER->CountLive() <= ceil(DB::$USER->Count() / 3);
  }
}
