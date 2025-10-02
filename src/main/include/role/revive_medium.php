<?php
/*
  ◆風祝 (revive_medium)
  ○仕様
  ・蘇生率：25% / 誤爆有り
*/
RoleManager::LoadFile('medium');
class Role_revive_medium extends Role_medium {
  public $mix_in = 'poison_cat';

  protected function OutputAddResult() {
    $this->filter->OutputReviveResult();
  }

  public function OutputAction() {
    $this->filter->OutputAction();
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
}
