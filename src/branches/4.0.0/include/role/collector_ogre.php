<?php
/*
  ◆蒐集鬼 (collector_ogre)
  ○仕様
  ・勝利：生存 + 生存者の役職系に重複なし
  ・人攫い成功率低下：3/5
  ・人狼襲撃無効確率：25%
  ・暗殺反射確率：45%
*/
RoleLoader::LoadFile('ogre');
class Role_collector_ogre extends Role_ogre {
  protected function GetOgreWolfEatResistRate() {
    return 25;
  }

  public function GetReflectAssassinRate() {
    return 45;
  }

  protected function GetOgreReduceNumerator() {
    return 3;
  }

  protected function IgnoreOgreLoseAllDead() {
    return true;
  }

  protected function OgreWin() {
    $stack = [];
    foreach (DB::$USER->SearchLive() as $id => $uname) {
      $role = RoleDataManager::GetGroup(DB::$USER->ByID($id)->GetMainRole());
      if (in_array($role, $stack)) {
	return false;
      }
      $stack[] = $role;
    }

    return true;
  }
}
