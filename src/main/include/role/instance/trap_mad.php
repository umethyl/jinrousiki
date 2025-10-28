<?php
/*
  ◆罠師 (trap_mad)
  ○仕様
  ・罠：罠死
*/
class Role_trap_mad extends Role {
  public $action     = VoteAction::TRAP;
  public $not_action = VoteAction::NOT_TRAP;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IsAddVote() {
    return false === $this->IgnoreTrap();
  }

  //罠能力無効判定
  protected function IgnoreTrap() {
    return false === $this->IsActorActive();
  }

  public function OutputAction() {
    $str = RoleAbilityMessage::TRAP;
    RoleHTML::OutputVoteNight(VoteCSS::WOLF, $str, $this->action, $this->not_action);
  }

  protected function GetDisabledAddVoteNightMessage() {
    return VoteRoleMessage::LOST_ABILITY;
  }

  protected function DisableVoteNightCheckboxSelf() {
    return false;
  }

  protected function IgnoreCompletedVoteNight() {
    return $this->IgnoreTrap();
  }

  //罠設置
  final public function SetTrap(User $user) {
    //人狼に狙われていたら自己設置以外は無効
    if ($this->IsActor($this->GetWolfTarget()) && false === $this->IsActor($user)) {
      return;
    }

    $this->AddStack($user->id, $this->GetTrapType());
    $this->SetTrapAction();
  }

  //罠設置種取得
  protected function GetTrapType() {
    return RoleVoteTarget::TRAP;
  }

  //罠設置後処理
  protected function SetTrapAction() {
    $this->GetActor()->LostAbility();
  }

  //罠発動判定 (罠能力者相互)
  final public function TrapToTrap() {
    //同種判定 (設置先に罠があった場合は罠にかかる / 自己設置は除外)
    $stack = $this->GetStack($this->GetTrapType());
    $count = array_count_values($stack);
    foreach ($stack as $id => $target_id) {
      if ($id != $target_id && $count[$target_id] > 1) {
	$this->AddSuccess($id, $this->GetTrapActionType());
      }
    }

    //他種判定
    foreach ($this->GetStack($this->GetOtherTrapType()) as $id => $target_id) {
      if ($id != $target_id && in_array($target_id, $stack)) {
	$this->AddSuccess($id, $this->GetTrapActionType());
      }
    }
  }

  //他罠設置種取得
  protected function GetOtherTrapType() {
    return RoleVoteTarget::SNOW_TRAP;
  }

  //罠発動種取得
  protected function GetTrapActionType() {
    return RoleVoteSuccess::TRAPPED;
  }

  //罠発動
  final public function TrapKill(User $user, $id, $delay = false) {
    $flag = $this->IsTrap($id);
    if (true === $flag) {
      if (true === $delay) {
	$this->AddSuccess($user->id, $this->GetTrapActionType());
      } else {
	$this->TrapKillAction($user);
      }
    }
    return $this->GetTrapKillResult($flag);
  }

  //罠発動判定
  final protected function IsTrap($id) {
    return $this->InStack($id, $this->GetTrapType());
  }

  //罠発動実行処理
  protected function TrapKillAction(User $user) {
    DB::$USER->Kill($user->id, DeadReason::TRAPPED);
  }

  //罠発動後ステータス取得
  protected function GetTrapKillResult($flag) {
    return $flag;
  }

  //罠発動 (遅行型)
  final public function DelayTrap(User $user, $id) {
    return $this->TrapKill($user, $id, true);
  }

  //罠発動 (複合型)
  final public function TrapComposite(User $user, $id) {
    return $this->TrapCompositeAction($user, $id);
  }

  //罠発動実行処理 (複合型)
  protected function TrapCompositeAction(User $user, $id) {
    return $this->TrapKill($user, $id);
  }

  //罠発動処理 (遅行型)
  final public function DelayTrapKill() {
    $this->DelayTrapKillAction();
  }

  //罠発動実行処理 (遅行型)
  protected function DelayTrapKillAction() {
    foreach ($this->GetStack($this->GetTrapActionType()) as $id => $flag) {
      DB::$USER->Kill($id, DeadReason::TRAPPED);
    }
    $this->SetStack([], $this->GetTrapActionType()); //リストをリセット
  }
}
