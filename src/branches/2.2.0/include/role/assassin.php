<?php
/*
  ◆暗殺者 (assassin)
  ○仕様
  ・暗殺：標準
  ・暗殺失敗：通常
*/
class Role_assassin extends Role {
  public $action     = 'ASSASSIN_DO';
  public $not_action = 'ASSASSIN_NOT_DO';

  function OutputAction() {
    RoleHTML::OutputVote('assassin-do', 'assassin_do', $this->action, $this->not_action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  function GetIgnoreMessage() { return '初日は暗殺できません'; }

  function ExistsActionFilter(array $list) {
    if (DB::$ROOM->IsEvent('force_assassin_do')) unset($list[$this->not_action]);
    return $list;
  }

  function SetVoteNightFilter() {
    if (DB::$ROOM->IsEvent('force_assassin_do')) $this->SetStack(null, 'not_action');
  }

  //暗殺先セット
  function SetAssassin(User $user) {
    $actor = $this->GetActor();
    foreach (RoleManager::LoadFilter('trap') as $filter) { //罠判定
      if ($filter->TrapStack($actor, $user->id)) return;
    }
    foreach (RoleManager::LoadFilter('guard_assassin') as $filter) { //対暗殺護衛判定
      if ($filter->GuardAssassin($user->id)) return;
    }
    if ($user->IsMainGroup('escaper')) return; //逃亡者は無効
    if ($user->IsReflectAssassin() || $this->IsReflectAssassin()) { //反射判定
      $this->AddSuccess($actor->id, 'assassin');
      return;
    }
    $class = $this->GetClass($method = 'Assassin');
    $class->$method($user);
  }

  //暗殺反射判定
  protected function IsReflectAssassin() { return false; }

  //暗殺処理
  function Assassin(User $user) {
    if ($user->IsDead(true) || $this->IgnoreAssassin($user)) return false;
    $this->SetAssassinTarget($user);
    $this->AssassinAction($user);
    return true;
  }

  //暗殺失敗判定
  protected function IgnoreAssassin(User $user) { return false; }

  //暗殺死対象セット
  protected function SetAssassinTarget(User $user) { $this->AddSuccess($user->id, 'assassin'); }

  //暗殺追加処理
  protected function AssassinAction(User $user) {}

  //暗殺死処理
  function AssassinKill() {
    foreach ($this->GetStack() as $id => $flag) DB::$USER->Kill($id, 'ASSASSIN_KILLED');
  }
}
