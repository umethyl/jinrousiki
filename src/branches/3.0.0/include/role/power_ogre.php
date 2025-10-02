<?php
/*
  ◆星熊童子 (power_ogre)
  ○仕様
  ・勝利：生存 + 人口を三分の一以下にする
*/
RoleManager::LoadFile('ogre');
class Role_power_ogre extends Role_ogre {
  public $resist_rate  = 40;
  public $reduce_base  =  7;
  public $reduce_rate  = 10;
  public $reflect_rate = 40;

  public function Win($winner) {
    return $this->IsLive() &&
      count(DB::$USER->GetLivingUsers()) <= ceil(DB::$USER->GetUserCount() / 3);
  }
}
