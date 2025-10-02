<?php
/*
  ◆翠狐 (emerald_fox)
  ○仕様
  ・占い：共鳴者付加 (念話できない妖狐カウント)
*/
RoleLoader::LoadFile('fox');
class Role_emerald_fox extends Role_fox {
  public $mix_in = ['mage'];
  public $action = VoteAction::MAGE;

  protected function IsAddVote() {
    return $this->IsActorActive();
  }

  public function OutputAction() {
    RoleHTML::OutputVoteNight(VoteCSS::MAGE, RoleAbilityMessage::MAGE, $this->action);
  }

  protected function GetDisabledAddVoteNightMessage() {
    return VoteRoleMessage::LOST_ABILITY;
  }

  protected function IgnoreCompletedVoteNight() {
    return false === $this->IsAddVote();
  }

  public function MageFailed(User $user) {
    return false;
  }

  public function MageSuccess(User $user) {
    if (false === RoleUser::IsFoxCount($user) ||
	false === ($user->IsMainGroup(CampGroup::CHILD_FOX) || RoleUser::IsLonely($user))) {
      return false;
    }

    $role = $this->GetActor()->GetID('mind_friend');
    $this->GetActor()->LostAbility();
    $this->GetActor()->AddRole($role);
    $user->AddRole($role);
  }
}
