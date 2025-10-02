<?php
/*
  ◆神主 (bacchus_medium)
  ○仕様
  ・処刑投票：ショック死 (鬼陣営)
*/
RoleLoader::LoadFile('medium');
class Role_bacchus_medium extends Role_medium {
  public $mix_in = ['critical_mad', 'chicken'];

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  protected function IgnoreVoteKillAction(User $user) {
    return RoleUser::IsAvoidLovers($user, true);
  }

  protected function IsVoteKillActionTarget(User $user) {
    return $user->IsMainCamp(Camp::OGRE);
  }

  protected function SetVoteKillAction(User $user) {
    $this->SuddenDeathKill($user->id);
  }

  protected function GetSuddenDeathType() {
    return 'DRUNK';
  }
}
