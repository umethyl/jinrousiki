<?php
/*
  ◆寒戸婆 (snow_cupid)
  ○仕様
  ・処刑投票：凍傷 (恋人)
*/
RoleLoader::LoadFile('cupid');
class Role_snow_cupid extends Role_cupid {
  public $mix_in = ['critical_mad'];

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  protected function IgnoreVoteKillAction(User $user) {
    return RoleUser::IsAvoidLovers($user, true);
  }

  protected function IsVoteKillActionTarget(User $user) {
    return $user->IsRole('lovers');
  }

  protected function SetVoteKillAction(User $user) {
    $user->AddDoom(1, 'frostbite');
  }
}
