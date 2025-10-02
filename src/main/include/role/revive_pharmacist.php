<?php
/*
  ◆仙人 (revive_pharmacist)
  ○仕様
  ・ショック死抑制
  ・人狼襲撃：蘇生
*/
RoleManager::LoadFile('pharmacist');
class Role_revive_pharmacist extends Role_pharmacist {
  //復活処理
  function Resurrect() {
    $user = $this->GetActor();
    if (! $this->IsResurrect($user) || ! $user->IsActive()) return false;
    $user->Revive();
    $user->LostAbility();
    return true;
  }

  //復活判定
  function IsResurrect(User $user) {
    return $user->wolf_killed && $user->IsDead(true) && ! $user->IsDummyBoy() &&
      ! $user->IsLovers() && ! $this->GetWolfVoter()->IsSiriusWolf();
  }
}
