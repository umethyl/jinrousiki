<?php
/*
  ◆墓荒らし (grave_mad)
  ○仕様
  ・死者妨害：憑依能力者以外
*/
class Role_grave_mad extends Role {
  public $action     = VoteAction::GRAVE;
  public $not_action = VoteAction::NOT_GRAVE;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IsAddVote() {
    return DB::$ROOM->IsOption('not_open_cast') || DB::$ROOM->IsOption('auto_open_cast');
  }

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3 || false === $this->IsAddVote();
  }

  public function OutputAction() {
    $str = RoleAbilityMessage::GRAVE;
    RoleHTML::OutputVoteNight(VoteCSS::WOLF, $str, $this->action, $this->not_action);
  }

  protected function GetDisabledAddVoteNightMessage() {
    return VoteRoleMessage::OPEN_CAST;
  }

  protected function FixLiveVoteNightIconPath() {
    return true;
  }

  protected function IsVoteNightCheckboxLive($live) {
    return false === $live;
  }

  protected function DisableVoteNightCheckboxDummyBoy() {
    return true;
  }

  //死者妨害対象者セット
  public function SetGrave(User $user) {
    if (false === RoleUser::IsPossessed($user)) {
      $this->AddStack($user->id, 'grave');
    }
  }

  //死者妨害
  public function Grave(User $user) {
    if (false === RoleUser::IsAvoid($user) && Lottery::Percent(70)) {
      $user->AddDoom(2);
    }
  }
}
