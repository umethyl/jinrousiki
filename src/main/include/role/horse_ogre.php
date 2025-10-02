<?php
/*
  ◆馬頭鬼 (horse_ogre)
  ○仕様
  ・勝利：生存 + 毒能力者全滅 + 村人陣営の勝利
*/
RoleManager::LoadFile('ogre');
class Role_horse_ogre extends Role_ogre {
  function Win($winner) {
    if ($winner != 'human' || $this->IsDead()) return false;
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLive() && $user->IsRoleGroup('poison')) return false;
    }
    return true;
  }
}
