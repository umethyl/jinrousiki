<?php
/*
  ◆狡狐 (trap_fox)
  ○仕様
*/
RoleManager::LoadFile('fox');
class Role_trap_fox extends Role_fox {
  public $mix_in = 'trap_mad';

  function OutputAction() { $this->filter->OutputAction(); }

  function IsVote() { return $this->filter->IsVote(); }

  function IsFinishVote(array $list) { return $this->filter->IsFinishVote($list); }

  function SetVoteNight() { $this->filter->SetVoteNight(); }

  function IsVoteCheckbox(User $user, $live) {
    return $this->filter->IsVoteCheckbox($user, $live);
  }

  function IgnoreVoteNight(User $user, $live) {
    return $this->filter->IgnoreVoteNight($user, $live);
  }
}
