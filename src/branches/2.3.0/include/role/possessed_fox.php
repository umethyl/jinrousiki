<?php
/*
  ◆憑狐 (possessed_fox)
  ○仕様
  ・憑依無効陣営：人狼/恋人
*/
RoleManager::LoadFile('fox');
class Role_possessed_fox extends Role_fox {
  public $mix_in = 'possessed_mad';

  protected function OutputAddResult() {
    $this->filter->OutputPossessed();
  }

  public function OutputAction() {
    $this->filter->OutputAction();
  }

  public function IsMindReadPossessed(User $user) {
    return $this->GetTalkFlag('fox');
  }

  public function IsVote() {
    return $this->filter->IsVote();
  }

  public function SetVoteNight() {
    $this->filter->SetVoteNight();
  }

  public function GetVoteIconPath(User $user, $live) {
    return $this->filter->GetVoteIconPath($user, $live);
  }

  public function IsVoteCheckbox(User $user, $live) {
    return $this->filter->IsVoteCheckbox($user, $live);
  }

  public function IsFinishVote(array $list) {
    return $this->filter->IsFinishVote($list);
  }

  public function IgnoreVoteNight(User $user, $live) {
    return $this->filter->IgnoreVoteNight($user, $live);
  }

  public function IgnorePossessed($camp) {
    return $camp == 'wolf' || $camp == 'lovers';
  }
}
