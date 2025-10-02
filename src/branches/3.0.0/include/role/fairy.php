<?php
/*
  ◆妖精 (fairy)
  ○仕様
  ・悪戯：発言妨害 (共有者の囁き)
  ・発言変換：悪戯
*/
class Role_fairy extends Role {
  public $mix_in = array('mage');
  public $action = 'FAIRY_DO';
  public $bad_status = null;

  public function OutputAction() {
    RoleHTML::OutputVote('fairy-do', 'fairy_do', $this->action);
  }

  //発言変換 (悪戯)
  public function ConvertSay() {
    $this->SetStack($this->GetBadStatus() . $this->GetStack('say'), 'say');
  }

  //占い (悪戯)
  public function Mage(User $user) {
    if ($this->IsJammer($user) || $this->IsCursed($user)) return false;
    $this->FairyAction($user);
  }

  //悪戯
  protected function FairyAction(User $user) {
    $user->AddRole(sprintf('bad_status[%d-%d]', $this->GetID(), DB::$ROOM->date + 1));
  }

  //悪戯内容取得
  protected function GetBadStatus() {
    return is_null($this->bad_status) ? RoleTalkMessage::COMMON_TALK : $this->bad_status;
  }
}
