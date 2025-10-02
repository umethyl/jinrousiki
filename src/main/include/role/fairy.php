<?php
/*
  ◆妖精 (fairy)
  ○仕様
  ・悪戯：発言妨害 (共有者の囁き)
  ・発言変換：悪戯
*/
class Role_fairy extends Role {
  public $mix_in = 'mage';
  public $action = 'FAIRY_DO';
  public $bad_status = null;

  function OutputAction() {
    RoleHTML::OutputVote('fairy-do', 'fairy_do', $this->action);
  }

  //占い (悪戯)
  function Mage(User $user) {
    if ($this->IsJammer($user) || $this->IsCursed($user)) return false;
    $this->FairyAction($user);
  }

  //悪戯
  function FairyAction(User $user) {
    $user->AddRole(sprintf('bad_status[%d-%d]', $this->GetID(), DB::$ROOM->date + 1));
  }

  //発言変換 (悪戯)
  function ConvertSay() { $this->SetStack($this->GetBadStatus() . $this->GetStack('say'), 'say'); }

  //悪戯内容取得
  protected function GetBadStatus() {
    return is_null($this->bad_status) ? Message::$common_talk : $this->bad_status;
  }
}
