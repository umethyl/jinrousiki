<?php
/*
  ◆罠師 (trap_mad)
  ○仕様
*/
class Role_trap_mad extends Role {
  public $action     = 'TRAP_MAD_DO';
  public $not_action = 'TRAP_MAD_NOT_DO';
  public $submit     = 'trap_do';
  public $not_submit = 'trap_not_do';
  public $ignore_message = '初日は罠を設置できません';

  function OutputAction() {
    if ($this->IsVoteTrap()) {
      RoleHTML::OutputVote('wolf-eat', $this->submit, $this->action, $this->not_action);
    }
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  //罠能力判定
  protected function IsVoteTrap() { return $this->GetActor()->IsActive(); }

  function IsFinishVote(array $list) {
    return ! $this->IsVoteTrap() || parent::IsFinishVote($list);
  }

  function IgnoreVote() {
    if (! is_null($str = parent::IgnoreVote())) return $str;
    return $this->IsVoteTrap() ? null : '能力喪失しています';
  }

  function IsVoteCheckbox(User $user, $live) { return $live; }

  function IgnoreVoteNight(User $user, $live) { return $live ? null : '死者には投票できません'; }

  //罠設置
  function SetTrap($uname) {
    //人狼に狙われていたら自己設置以外は無効
    if ($this->IsActor($this->GetWolfTarget()->uname) && ! $this->IsActor($uname)) return;
    $this->SetTrapAction($this->GetActor(), $uname);
  }

  //罠設置後処理
  protected function SetTrapAction(User $user, $uname) {
    $this->AddStack($uname, 'trap', $user->uname);
    $user->LostAbility();
  }

  //罠能力者の罠判定
  function TrapToTrap() {
    //罠師が自分自身以外に罠を仕掛けた場合、設置先に罠があった場合は死亡
    $stack = $this->GetStack('trap');
    $count = array_count_values($stack);
    foreach ($stack as $uname => $target_uname) {
      if ($uname != $target_uname && $count[$target_uname] > 1) {
	$this->AddSuccess($uname, 'trapped');
      }
    }

    foreach($this->GetStack('snow_trap') as $uname => $target_uname) { //雪女の罠死判定
      if ($uname != $target_uname && in_array($target_uname, $stack)) {
	$this->AddSuccess($uname, 'trapped');
      }
    }
  }

  //罠死判定
  function TrapKill(User $user, $uname) {
    if ($flag = $this->IsTrap($uname)) DB::$USER->Kill($user->user_no, 'TRAPPED');
    return $flag;
  }

  //罠死リスト判定
  function DelayTrap(User $user, $uname) {
    if ($flag = $this->IsTrap($uname)) $this->AddSuccess($user->uname, 'trapped');
    return $flag;
  }

  //罠判定
  protected function IsTrap($uname) { return in_array($uname, $this->GetStack('trap')); }

  //罠死+凍傷リスト判定
  function TrapStack(User $user, $uname) { return $this->TrapKill($user, $uname); }

  //罠死リストの死亡処理
  function DelayTrapKill() {
    foreach ($this->GetStack('trapped') as $uname => $flag) {
      DB::$USER->Kill(DB::$USER->UnameToNumber($uname), 'TRAPPED');
    }
    $this->SetStack(array(), 'trapped'); //リストをリセット
  }
}
