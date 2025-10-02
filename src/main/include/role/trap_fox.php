<?php
/*
  ◆狡狐 (trap_fox)
  ○仕様
*/
RoleManager::LoadFile('fox');
class Role_trap_fox extends Role_fox {
  public $mix_in = 'trap_mad';

  public function OutputAction() {
    $this->filter->OutputAction();
  }

  public function IsVote() {
    return $this->filter->IsVote();
  }

  public function SetVoteNight() {
    $this->filter->SetVoteNight();
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
