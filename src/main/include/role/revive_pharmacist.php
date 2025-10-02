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
  final public function Resurrect() {
    if (! $this->IsResurrectTarget() || ! $this->CallParent('IsResurrect')) return false;

    $this->GetActor()->Revive();
    if ($this->CallParent('IsResurrectLost')) $this->GetActor()->LostAbility();
    $this->CallParent('ResurrectAction');
  }

  //復活判定
  public function IsResurrect() {
    return $this->GetActor()->IsActive();
  }

  //復活能力喪失判定
  public function IsResurrectLost() {
    return true;
  }

  //復活後処理
  public function ResurrectAction() {}

  //復活対象者判定
  private function IsResurrectTarget() {
    $user = $this->GetActor();
    return $user->wolf_killed && $user->IsDead(true) && ! $user->IsDummyBoy() &&
      ! $user->IsLovers() && ! $this->GetWolfVoter()->IsSiriusWolf();
  }
}
