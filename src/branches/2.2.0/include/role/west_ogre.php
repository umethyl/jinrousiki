<?php
/*
  ◆金鬼 (west_ogre)
  ○仕様
  ・勝利：生存 + 自分と同列の左側にいる人の全滅 + 村人陣営の勝利
*/
RoleManager::LoadFile('ogre');
class Role_west_ogre extends Role_ogre {
  public $resist_rate  = 40;
  public $reduce_rate  =  2;
  public $reflect_rate = 40;

  function Win($winner) {
    if ($winner != 'human' || $this->IsDead()) return false;
    $id = $this->GetID();
    foreach (DB::$USER->rows as $user) {
      if ($user->id >= $id) return true;
      if ($user->id > (ceil($id / 5) - 1) * 5 && $user->IsLive()) return false;
    }
    return true;
  }
}
