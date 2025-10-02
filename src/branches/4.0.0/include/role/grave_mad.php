<?php
/*
  ◆墓荒らし (grave_mad)
  ○仕様
  ・死者妨害：憑依能力者以外
*/
class Role_grave_mad extends Role {
  public $action      = VoteAction::GRAVE;
  public $not_action  = VoteAction::NOT_GRAVE;
  public $action_date = RoleActionDate::AFTER;

  protected function IsAddVote() {
    return DB::$ROOM->IsOption('not_open_cast') || DB::$ROOM->IsOption('auto_open_cast');
  }

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3 || ! $this->IsAddVote();
  }

  public function OutputAction() {
    $str = RoleAbilityMessage::GRAVE;
    RoleHTML::OutputVote(VoteCSS::WOLF, $str, $this->action, $this->not_action);
  }

  protected function GetDisabledAddVoteMessage() {
    return VoteRoleMessage::OPEN_CAST;
  }

  protected function IgnoreDeadVoteIconPath() {
    return true;
  }

  protected function IsVoteCheckboxLive($live) {
    return ! $live;
  }

  protected function IgnoreVoteCheckboxDummyBoy() {
    return true;
  }

  //死者妨害対象者セット
  public function SetGrave(User $user) {
    if (! RoleUser::IsPossessed($user)) $this->AddStack($user->id, 'grave');
  }

  //死者妨害
  public function Grave(User $user) {
    if (! RoleUser::IsAvoid($user) && Lottery::Percent(70)) $user->AddDoom(2);
  }
}
