<?php
/*
  ◆暗殺者 (assassin)
  ○仕様
  ・暗殺：標準
  ・暗殺失敗：通常
*/
class Role_assassin extends Role {
  public $action     = VoteAction::ASSASSIN;
  public $not_action = VoteAction::NOT_ASSASSIN;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  public function OutputAction() {
    $str = RoleAbilityMessage::ASSASSIN;
    RoleHTML::OutputVoteNight(VoteCSS::ASSASSIN, $str, $this->action, $this->not_action);
  }

  protected function DisableNotAction() {
    return DB::$ROOM->IsEvent('force_assassin_do');
  }

  //暗殺先セット (罠 > 対暗殺護衛 > 逃亡 > 反射 > 通常)
  final public function SetAssassin(User $user) {
    if (RoleUser::DelayTrap($this->GetActor(), $user->id)) {
      return false;
    } elseif (RoleUser::GuardAssassin($user)) {
      return false;
    } elseif (RoleUser::IsEscape($user)) {
      return false;
    } elseif (RoleUser::IsReflectAssassin($user) || $this->IsReflectAssassin()) {
      $this->AddSuccess($this->GetID(), RoleVoteSuccess::ASSASSIN);
      return false;
    } else {
      return $this->CallParent('Assassin', $user);
    }
  }

  //暗殺反射判定
  protected function IsReflectAssassin() {
    return false;
  }

  //暗殺処理
  protected function Assassin(User $user) {
    if ($user->IsDead(true) || $this->IgnoreAssassin($user)) {
      return false;
    }

    if ($this->IsAssassinKill()) {
      $this->AddSuccess($user->id, RoleVoteSuccess::ASSASSIN);
    }
    $this->AssassinAction($user);
    return true;
  }

  //暗殺失敗判定
  protected function IgnoreAssassin(User $user) {
    return false;
  }

  //暗殺死実行判定
  protected function IsAssassinKill() {
    return true;
  }

  //暗殺追加処理
  protected function AssassinAction(User $user) {}

  //暗殺死処理
  public function AssassinKill() {
    foreach ($this->GetStack() as $id => $flag) {
      DB::$USER->Kill($id, DeadReason::ASSASSIN_KILLED);
    }
  }
}
