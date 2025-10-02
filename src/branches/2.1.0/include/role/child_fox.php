<?php
/*
  ◆子狐 (child_fox)
  ○仕様
  ・人狼襲撃耐性：無し
  ・占い：通常
*/
RoleManager::LoadFile('fox');
class Role_child_fox extends Role_fox {
  public $mix_in = 'mage';
  public $resist_wolf = false;
  public $action = 'CHILD_FOX_DO';
  public $result = 'CHILD_FOX_RESULT';
  public $submit = 'mage_do';
  public $mage_failed = 'failed';

  protected function OutputResult() {
    if (isset($this->result) && DB::$ROOM->date > 1) $this->OutputAbilityResult($this->result);
  }

  function OutputAction() {
    RoleHTML::OutputVote('mage-do', $this->submit, $this->action);
  }

  function Mage(User $user) {
    if ($this->IsJammer($user)) {
      return $this->SaveMageResult($user, $this->mage_failed, $this->result);
    }
    if ($this->IsCursed($user)) return false;
    $result = mt_rand(0, 9) < 7 ? $this->GetMageResult($user) : $this->mage_failed;
    $this->SaveMageResult($user, $result, $this->result);
  }

  protected function GetMageResult(User $user) { return $this->DistinguishMage($user); }
}
