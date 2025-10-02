<?php
/*
  ◆暗殺者 (assassin)
  ○仕様
  ・暗殺：標準
*/
class Role_assassin extends Role {
  public $action     = 'ASSASSIN_DO';
  public $not_action = 'ASSASSIN_NOT_DO';
  public $ignore_message = '初日は暗殺できません';

  function OutputAction() {
    RoleHTML::OutputVote('assassin-do', 'assassin_do', $this->action, $this->not_action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  function IsFinishVote(array $list) {
    if (DB::$ROOM->IsEvent('force_assassin_do')) unset($list[$this->not_action]);
    return parent::IsFinishVote($list);
  }

  function SetVoteNight() {
    parent::SetVoteNight();
    if (DB::$ROOM->IsEvent('force_assassin_do')) $this->SetStack(null, 'not_action');
  }

  //暗殺先セット
  function SetAssassin(User $user) {
    $actor = $this->GetActor();
    foreach (RoleManager::LoadFilter('trap') as $filter) { //罠判定
      if ($filter->TrapStack($actor, $user->uname)) return;
    }
    foreach (RoleManager::LoadFilter('guard_assassin') as $filter) { //対暗殺護衛判定
      if ($filter->GuardAssassin($user->uname)) return;
    }
    if ($user->IsRoleGroup('escaper')) return; //逃亡者は無効
    if ($user->IsRefrectAssassin()) { //反射判定
      $this->AddSuccess($actor->user_no, 'assassin');
      return;
    }
    $class = $this->GetClass($method = 'Assassin');
    $class->$method($user);
  }

  //暗殺処理 (protected)
  function Assassin(User $user) {
    if ($flag = $user->IsLive(true)) $this->AddSuccess($user->user_no, 'assassin');
    return $flag;
  }

  //暗殺死処理
  function AssassinKill() {
    foreach ($this->GetStack() as $id => $flag) DB::$USER->Kill($id, 'ASSASSIN_KILLED');
  }
}
