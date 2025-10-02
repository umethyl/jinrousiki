<?php
/*
  ◆月兎 (jammer_mad)
  ○仕様
*/
class Role_jammer_mad extends Role {
  public $action = VoteAction::JAMMER;

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::WOLF, RoleAbilityMessage::JAMMER, $this->action);
  }

  //妨害対象セット (呪返し > 妨害無効 > 成立判定)
  final public function SetJammer(User $user) {
    if (RoleUser::IsCursed($user) || $this->InStack($user->id, 'voodoo')) {
      RoleUser::GuardCurse($this->GetActor()); //厄払い判定
      return false;
    } elseif (RoleUser::GuardCurse($user, false)) {
      return false;
    } elseif ($this->CallParent('IsSetJammer')) {
      $this->AddStack($user->id, 'jammer');
    }
  }

  //妨害対象セット成立判定
  protected function IsSetJammer() {
    return true;
  }
}
