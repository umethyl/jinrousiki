<?php
/*
  ◆山わろ (poison_avenger)
  ○仕様
  ・毒：人外カウント + 自分の仇敵
*/
RoleManager::LoadFile('avenger');
class Role_poison_avenger extends Role_avenger {
  public $mix_in = 'poison';

  function IsPoisonTarget(User $user) {
    return $user->IsInhuman() || $user->IsPartner('enemy', $this->GetID());
  }
}
