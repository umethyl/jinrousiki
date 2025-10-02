<?php
/*
  ◆月狐 (jammer_fox)
  ○仕様
  ・占い妨害：70%
*/
RoleManager::LoadFile('child_fox');
class Role_jammer_fox extends Role_child_fox {
  public $mix_in = 'jammer_mad';
  public $result = null;

  public function OutputAction() {
    $this->filter->OutputAction();
  }

  public function SetVoteNight() {
    $this->filter->SetVoteNight();
  }

  public function IsFinishVote(array $list) {
    return $this->filter->IsFinishVote($list);
  }

  public function IsJammer(User $user) {
    return $this->filter->IsJammer($user) && Lottery::Percent(70);
  }
}
