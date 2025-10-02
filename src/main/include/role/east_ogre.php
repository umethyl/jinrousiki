<?php
/*
  ◆風鬼 (east_ogre)
  ○仕様
  ・勝利条件：生存 + 自分と同列の右側にいる人の全滅 + 村人陣営勝利
  ・人攫い成功率低下：1/2
  ・人狼襲撃無効確率：40%
  ・暗殺反射確率：40%
*/
RoleLoader::LoadFile('ogre');
class Role_east_ogre extends Role_ogre {
  protected function GetOgreResistWolfEatRate() {
    return 40;
  }

  public function GetReflectAssassinRate() {
    return 40;
  }

  protected function GetOgreReduceDenominator() {
    return 2;
  }

  protected function IsOgreLoseCamp($winner) {
    return $winner != WinCamp::HUMAN;
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }

  protected function OgreWin() {
    foreach (Position::GetEast($this->GetID()) as $id) {
      if (DB::$USER->ById($id)->IsLive()) {
	return false;
      }
    }
    return true;
  }
}
