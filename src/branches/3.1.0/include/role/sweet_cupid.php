<?php
/*
  ◆弁財天 (sweet_cupid)
  ○仕様
  ・追加役職：共鳴者 (両方)
  ・処刑投票：恋耳鳴
*/
RoleLoader::LoadFile('cupid');
class Role_sweet_cupid extends Role_cupid {
  public $mix_in = array('critical_mad');

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  protected function IgnoreVoteKillAction(User $user) {
    return false;
  }

  protected function GetVoteKillActionRole() {
    return 'sweet_ringing';
  }

  protected function AddCupidRole(User $user) {
    $user->AddRole($this->GetActor()->GetID('mind_friend'));
  }
}
