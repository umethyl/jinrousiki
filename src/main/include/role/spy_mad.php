<?php
/*
  ◆内通者 (spy_mad)
  ○仕様
  ・仲間表示：狂信者準拠
  ・勝利：離脱後一定日数以内に決着 (人口の1/8の切り上げ / 下限2)
*/
RoleLoader::LoadFile('fanatic_mad');
class Role_spy_mad extends Role_fanatic_mad {
  public $action     = VoteAction::EXIT_DO;
  public $not_action = VoteAction::NOT_EXIT;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IsAddVote() {
    $user = $this->GetActor();
    return false === RoleUser::IsContainLovers($user) &&
      false === is_array($user->GetPartner($this->role));
  }

  public function OutputAction() {
    $str = RoleAbilityMessage::SPY;
    RoleHTML::OutputVoteNight(VoteCSS::ESCAPE, $str, $this->action, $this->not_action);
  }

  protected function GetDisabledAddVoteNightMessage() {
    return VoteRoleMessage::LOST_ABILITY;
  }

  protected function GetVoteNightTargetUserFilter(array $list) {
    $id = $this->GetID();
    return [$id => $list[$id]];
  }

  protected function DisableVoteNightCheckboxSelf() {
    return false;
  }

  protected function CheckedVoteNightCheckbox(User $user) {
    return $this->IsActor($user);
  }

  public function ValidateVoteNightTargetFilter(User $user) {
    if (false === $this->IsActor($user)) {
      throw new UnexpectedValueException(VoteRoleMessage::TARGET_INCLUDE_MYSELF);
    }
  }

  //離脱
  public function ExecuteExit() {
    $user = $this->GetActor();
    $user->AddMainRole(DB::$ROOM->date);
    DB::$USER->Kill($user->id, DeadReason::TENGU_KILLED);
  }

  public function Win($winner) {
    $this->SetStack('escaper', 'class');
    $stack = $this->GetActor()->GetPartner($this->role);
    if (false === is_array($stack)) {
      return false;
    }

    $date = array_shift($stack);
    return DB::$ROOM->date <= $date + max(2, ceil(DB::$USER->Count() / 8));
  }
}
