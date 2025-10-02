<?php
/*
  ◆屍鬼 (scarlet_vampire)
  ○仕様
  ・人狼襲撃：確率蘇生
*/
RoleManager::LoadFile('vampire');
class Role_scarlet_vampire extends Role_vampire {
  public $mix_in = 'revive_pharmacist';

  function Resurrect() {
    $user = $this->GetActor();
    if ($this->IsResurrect($user) && Lottery::Percent(40)) $user->Revive();
  }
}
