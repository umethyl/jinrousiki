<?php
/*
  ◆山わろ (poison_avenger)
  ○仕様
  ・毒：人外カウント or 自分の仇敵
*/
RoleLoader::LoadFile('avenger');
class Role_poison_avenger extends Role_avenger {
  public $mix_in = ['poison'];

  protected function IsPoisonTarget(User $user) {
    return RoleUser::IsInhuman($user) || $user->IsPartner($this->GetPartnerRole(), $this->GetID());
  }
}
