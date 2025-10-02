<?php
/*
  ◆屍鬼 (scarlet_vampire)
  ○仕様
  ・人狼襲撃：確率蘇生
*/
RoleManager::LoadFile('vampire');
class Role_scarlet_vampire extends Role_vampire {
  public $mix_in = 'revive_pharmacist';

  public function Resurrect() {
    $user = $this->GetActor();
    $rate = DB::$ROOM->IsEvent('full_revive') ? 100 : 40;
    if ($this->IsResurrect($user) && Lottery::Percent($rate)) $user->Revive();
  }
}
