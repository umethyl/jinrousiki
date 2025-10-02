<?php
/*
  ◆百々爺 (poison_vampire)
  ○仕様
  ・毒：自分の感染者 + 洗脳者
*/
RoleManager::LoadFile('vampire');
class Role_poison_vampire extends Role_vampire {
  public $mix_in = 'poison';

  function IsPoisonTarget(User $user) {
    return $user->IsRole('psycho_infected') || $user->IsPartner('infected', $this->GetID());
  }
}
