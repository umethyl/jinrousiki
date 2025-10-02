<?php
/*
  ◆子狐 (child_fox)
  ○仕様
  ・人狼襲撃耐性：無し
  ・占い：通常
*/
RoleLoader::LoadFile('fox');
class Role_child_fox extends Role_fox {
  public $mix_in = array('mage');
  public $action = VoteAction::CHILD_FOX;
  public $result = RoleAbility::CHILD_FOX;
  public $submit = VoteAction::MAGE;

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2;
  }

  public function OutputAction() {
    if ($this->ExistVoteMix()) return $this->CallVoteMix(__FUNCTION__);
    RoleHTML::OutputVote(VoteCSS::MAGE, RoleAbilityMessage::MAGE, $this->action);
  }

  public function IsResistWolf() {
    return false;
  }

  public function Mage(User $user) {
    if ($this->IsJammer($user)) {
      return $this->SaveMageResult($user, $this->GetMageFailed(), $this->result);
    } elseif ($this->IsCursed($user)) {
      return false;
    } else {
      $result = Lottery::Percent(70) ? $this->GetMageResult($user) : $this->GetMageFailed();
      return $this->SaveMageResult($user, $result, $this->result);
    }
  }

  protected function GetMageFailed() {
    return 'failed';
  }

  protected function GetMageResult(User $user) {
    return $this->DistinguishMage($user);
  }
}
