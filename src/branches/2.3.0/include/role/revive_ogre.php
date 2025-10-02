<?php
/*
  ◆茨木童子 (revive_ogre)
  ○仕様
  ・勝利：生存 + 嘘吐き全滅
  ・人狼襲撃：確率蘇生
*/
RoleManager::LoadFile('ogre');
class Role_revive_ogre extends Role_ogre {
  public $mix_in = 'revive_pharmacist';
  public $resist_rate  =  0;
  public $reduce_rate  =  2;
  public $reflect_rate = 40;

  public function Win($winner) {
    if ($this->IsDead()) return false;
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLive() && $user->IsLiar()) return false;
    }
    return true;
  }

  public function Resurrect() {
    $user = $this->GetActor();
    $rate = is_null($event = $this->GetEvent()) ? 40 : $event;
    if ($this->IsResurrect($user) && Lottery::Percent($rate)) $user->Revive();
  }
}
