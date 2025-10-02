<?php
/*
  ◆仙人 (revive_pharmacist)
  ○仕様
  ・ショック死抑制
  ・人狼襲撃：蘇生
  ・復活無効：生存 or 恋人
*/
RoleLoader::LoadFile('pharmacist');
class Role_revive_pharmacist extends Role_pharmacist {
  //復活処理
  final public function Resurrect() {
    //無効判定 (身代わり君 > 人狼襲撃失敗 > 覚醒天狼襲撃 > 無効判定 > 能力判定)
    $user = $this->GetActor();
    if ($user->IsDummyBoy()) {
      return false;
    } elseif (! $user->wolf_killed) {
      return false;
    } elseif (RoleUser::IsSiriusWolf($this->GetWolfVoter())) {
      return false;
    } elseif ($this->CallParent('IgnoreResurrect')) {
      return false;
    } elseif (! $this->CallParent('IsResurrect')) {
      return false;
    }

    $this->CallParent('ResurrectRevive');
    if ($this->CallParent('IsResurrectLost')) {
      $this->GetActor()->LostAbility();
    }
    $this->CallParent('ResurrectAction');
  }

  //復活無効判定
  protected function IgnoreResurrect() {
    $user = $this->GetActor();
    return $user->IsLive(true) || $user->IsRole('lovers');
  }

  //復活能力判定
  protected function IsResurrect() {
    return $this->IsActorActive();
  }

  //復活処理
  protected function ResurrectRevive() {
    $this->GetActor()->Revive();
  }

  //復活能力喪失判定
  protected function IsResurrectLost() {
    return true;
  }

  //復活後処理
  protected function ResurrectAction() {}
}
