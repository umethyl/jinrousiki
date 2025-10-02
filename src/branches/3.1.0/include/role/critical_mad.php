<?php
/*
  ◆釣瓶落とし (critical_mad)
  ○仕様
  ・処刑投票：痛恨
*/
class Role_critical_mad extends Role {
  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname)) continue;
      $user = DB::$USER->ByRealUname($target_uname);
      if ($user->IsDead(true) || $this->CallParent('IgnoreVoteKillAction', $user)) continue;

      if ($this->CallParent('IsVoteKillActionTarget', $user)) {
	$this->CallParent('SetVoteKillAction', $user);
      }
    }
  }

  //処刑投票能力スキップ判定
  protected function IgnoreVoteKillAction(User $user) {
    return RoleUser::IsAvoid($user);
  }

  //処刑投票能力対象者判定
  protected function IsVoteKillActionTarget(User $user) {
    return true;
  }

  //処刑投票能力
  protected function SetVoteKillAction(User $user) {
    $user->AddRole($this->CallParent('GetVoteKillActionRole'));
  }

  //処刑投票能力追加役職
  protected function GetVoteKillActionRole() {
    return 'critical_luck';
  }
}
