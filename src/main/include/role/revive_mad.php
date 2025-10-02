<?php
/*
  ◆尸解仙 (revive_mad)
  ○仕様
  ・人狼襲撃：蘇生 + 共鳴
*/
class Role_revive_mad extends Role {
  public $mix_in = 'revive_pharmacist';

  function Resurrect() {
    if (! $this->filter->Resurrect()) return false;

    //共鳴処理
    $role = $this->GetActor()->GetID('mind_friend');
    $this->GetActor()->AddRole($role);
    $this->GetWolfVoter()->AddRole($role);
  }
}
