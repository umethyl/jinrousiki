<?php
/*
  ◆尸解仙 (revive_mad)
  ○仕様
  ・人狼襲撃：蘇生 + 共鳴
*/
class Role_revive_mad extends Role {
  public $mix_in = array('revive_pharmacist');

  public function ResurrectAction() {
    $role = $this->GetActor()->GetID('mind_friend');
    $this->GetActor()->AddRole($role);
    $this->GetWolfVoter()->AddRole($role);
  }
}
