<?php
/*
  ◆牛頭鬼 (cow_ogre)
  ○仕様
  ・勝利：生存 + 暗殺者系全滅 + 村人陣営の勝利
*/
RoleManager::LoadFile('ogre');
class Role_cow_ogre extends Role_ogre {
  public $reflect_rate = 45;

  function Win($winner) {
    if ($winner != 'human' || $this->IsDead()) return false;
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLive() && $user->IsMainGroup('assassin')) return false;
    }
    return true;
  }
}
