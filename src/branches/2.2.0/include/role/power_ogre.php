<?php
/*
  ◆星熊童子 (power_ogre)
  ○仕様
  ・勝利：生存 + 人口を三分の一以下にする
*/
RoleManager::LoadFile('ogre');
class Role_power_ogre extends Role_ogre {
  public $resist_rate  = 40;
  public $reflect_rate = 40;

  function Win($winner) {
    return $this->IsLive() &&
      count(DB::$USER->GetLivingUsers()) <= ceil(count(DB::$USER->rows) / 3);
  }

  protected function GetReduceRate() { return 7 / 10; }
}
