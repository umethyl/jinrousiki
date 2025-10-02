<?php
/*
  ◆弁財天 (sweet_cupid)
  ○仕様
  ・追加役職：共鳴者 (両方)
  ・処刑投票：恋耳鳴
*/
RoleManager::LoadFile('cupid');
class Role_sweet_cupid extends Role_cupid {
  public $mix_in = 'critical_mad';
  public $vote_day_type = 'init';

  protected function AddCupidRole(User $user) {
    $user->AddRole($this->GetActor()->GetID('mind_friend'));
  }

  public function SetVoteAction(User $user) {
    $user->AddRole('sweet_ringing');
  }
}
