<?php
/*
  ◆妖精 (fairy)
  ○仕様
  ・発言変換：悪戯
  ・占い：悪戯
  ・悪戯：発言妨害 (共有者の囁き)
*/
class Role_fairy extends Role {
  public $mix_in = ['mage'];
  public $action = VoteAction::FAIRY;

  protected function IsAddVote() {
    return $this->CallParent('IsFairyVote');
  }

  //投票能力判定 (悪戯能力者専用)
  protected function IsFairyVote() {
    return true;
  }

  public function OutputAction() {
    RoleHTML::OutputVoteNight(VoteCSS::FAIRY, RoleAbilityMessage::FAIRY, $this->action);
  }

  protected function GetDisabledAddVoteNightMessage() {
    return $this->CallParent('GetDisabledFairyVoteNightMessage');
  }

  //夜投票無効メッセージ取得 (悪戯能力者専用)
  protected function GetDisabledFairyVoteNightMessage() {
    return null;
  }

  //発言変換 (悪戯)
  public function ConvertSay() {
    $this->SetStack($this->GetBadStatus() . $this->GetStack('say'), 'say');
  }

  //悪戯内容取得
  protected function GetBadStatus() {
    return RoleTalkMessage::COMMON_TALK;
  }

  //占い(悪戯) (妨害 > 呪返し > 悪戯)
  public function Mage(User $user) {
    if ($this->IsJammer($user) || $this->IsCursed($user)) {
      return false;
    }
    $this->CallParent('FairyAction', $user);
  }

  //悪戯
  protected function FairyAction(User $user) {
    $user->AddRole(sprintf('bad_status[%d-%d]', $this->GetID(), DB::$ROOM->date + 1));
  }
}
