<?php
/*
  ◆翠狐 (emerald_fox)
  ○仕様
  ・占い：共鳴者付加
*/
RoleManager::LoadFile('fox');
class Role_emerald_fox extends Role_fox {
  public $mix_in = 'mage';
  public $action = 'MAGE_DO';

  public function OutputAction() {
    if ($this->GetActor()->IsActive()) RoleHTML::OutputVote('mage-do', 'mage_do', $this->action);
  }

  protected function IgnoreVoteFilter() {
    return $this->GetActor()->IsActive() ? null : VoteRoleMessage::LOST_ABILITY;
  }

  protected function IgnoreFinishVote() {
    return ! $this->GetActor()->IsActive();
  }

  public function Mage(User $user) {
    if ($this->IsJammer($user) || $this->IsCursed($user)) return false;
    if (! $user->IsFox() || ! ($user->IsChildFox() || $user->IsLonely())) return false;

    $role = $this->GetActor()->GetID('mind_friend');
    $this->GetActor()->LostAbility();
    $this->GetActor()->AddRole($role);
    $user->AddRole($role);
  }
}
