<?php
/*
  ◆罠師 (trap_mad)
  ○仕様
  ・罠：罠死
*/
class Role_trap_mad extends Role {
  public $action      = 'TRAP_MAD_DO';
  public $not_action  = 'TRAP_MAD_NOT_DO';
  public $action_date_type = 'after';
  public $submit      = 'trap_do';
  public $not_submit  = 'trap_not_do';
  public $trap_action = 'trap';
  public $trap_result = 'trapped';

  public function OutputAction() {
    if (! $this->IgnoreTrap()) {
      RoleHTML::OutputVote('wolf-eat', $this->submit, $this->action, $this->not_action);
    }
  }

  protected function IgnoreVoteFilter() {
    return $this->IgnoreTrap() ? VoteRoleMessage::LOST_ABILITY : null;
  }

  public function IsVoteCheckbox(User $user, $live) {
    return $live;
  }

  protected function IgnoreFinishVote() {
    return $this->IgnoreTrap();
  }

  public function IgnoreVoteNight(User $user, $live) {
    return $live ? null : VoteRoleMessage::TARGET_DEAD;
  }

  //罠能力無効判定
  protected function IgnoreTrap() {
    return ! $this->GetActor()->IsActive();
  }

  //罠設置
  public function SetTrap(User $user) {
    //人狼に狙われていたら自己設置以外は無効
    if ($this->IsActor($this->GetWolfTarget()) && ! $this->IsActor($user)) return;
    $this->AddStack($user->id, $this->trap_action);
    $this->SetTrapAction();
  }

  //罠設置後処理
  protected function SetTrapAction() {
    $this->GetActor()->LostAbility();
  }

  //罠能力者の罠判定
  public function TrapToTrap() {
    //同種判定 (自分自身以外に仕掛けた場合、設置先に罠があった場合は罠にかかる)
    $stack = $this->GetStack($this->trap_action);
    $count = array_count_values($stack);
    foreach ($stack as $id => $target_id) {
      if ($id != $target_id && $count[$target_id] > 1) {
	$this->AddSuccess($id, $this->trap_result);
      }
    }

    //他種判定
    foreach ($this->GetStack($this->GetOtherTrap()) as $id => $target_id) {
      if ($id != $target_id && in_array($target_id, $stack)) {
	$this->AddSuccess($id, $this->trap_result);
      }
    }
  }

  //他種罠タイプ取得
  protected function GetOtherTrap() {
    return 'snow_trap';
  }

  //罠発動
  public function TrapKill(User $user, $id) {
    if ($flag = $this->IsTrap($id)) DB::$USER->Kill($user->id, 'TRAPPED');
    return $flag;
  }

  //罠発動 (遅行発動型)
  public function DelayTrap(User $user, $id) {
    if ($flag = $this->IsTrap($id)) $this->AddSuccess($user->id, $this->trap_result);
    return $flag;
  }

  //罠発動判定
  protected function IsTrap($id) {
    return in_array($id, $this->GetStack($this->trap_action));
  }

  //罠発動 (複合発動型)
  public function TrapStack(User $user, $id) {
    return $this->TrapKill($user, $id);
  }

  //遅行発動型の罠処理
  public function DelayTrapKill() {
    foreach ($this->GetStack($this->trap_result) as $id => $flag) {
      DB::$USER->Kill($id, 'TRAPPED');
    }
    $this->SetStack(array(), $this->trap_result); //リストをリセット
  }
}
