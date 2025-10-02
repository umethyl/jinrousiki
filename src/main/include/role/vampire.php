<?php
/*
  ◆吸血鬼 (vampire)
  ○仕様
  ・吸血：通常
  ・仲間表示：感染者・洗脳者
*/
class Role_vampire extends Role {
  public $action = 'VAMPIRE_DO';
  public $ignore_message = '初日は襲撃できません';

  protected function OutputPartner() {
    /* 2日目の時点で感染者・洗脳者が発生する特殊イベントを実装したら対応すること */
    if (DB::$ROOM->date < 2) return;
    $id = $this->GetID();
    $partner = 'infected';
    $role    = 'psycho_infected';
    $partner_list = array();
    $role_list    = array();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsPartner($partner, $id)) $partner_list[] = $user->handle_name;
      if ($user->IsRole($role)) $role_list[] = $user->handle_name;
    }
    RoleHTML::OutputPartner($partner_list, $partner . '_list');
    RoleHTML::OutputPartner($role_list, $role . '_list');
  }

  function OutputAction() {
    RoleHTML::OutputVote('vampire-do', 'vampire_do', $this->action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  //吸血対象セット
  function SetInfect(User $user) {
    $actor = $this->GetActor();
    $this->SetStack($actor, 'voter');
    foreach (RoleManager::LoadFilter('trap') as $filter) { //罠判定
      if ($filter->DelayTrap($actor, $user->uname)) return;
    }
    foreach (array_keys($this->GetStack('escaper'), $actor->uname) as $uname) { //自己逃亡判定
      $this->SetInfectTarget($uname);
    }
    foreach (array_keys($this->GetStack('escaper'), $user->uname) as $uname) { //逃亡巻き添え判定
      $this->SetInfectTarget($uname);
    }
    if (RoleManager::GetClass('guard')->Guard($user, true)) return; //護衛判定
    if ($user->IsDead(true) || $user->IsRoleGroup('escaper')) return; //スキップ判定

    //吸血リスト登録
    if ($user->IsRoleGroup('vampire')) {
      RoleManager::LoadMain($user)->InfectVampire($actor); //吸血鬼襲撃
    }
    elseif ($user->IsRole('soul_mania', 'dummy_mania') && $user->IsCamp('vampire')) {
      $this->AddSuccess($user->user_no, 'vampire_kill'); //覚醒コピー能力者
    }
    else {
      $this->SetInfectTarget($user->uname);
    }
  }

  //吸血リスト登録
  protected function SetInfectTarget($uname) {
    $stack = $this->GetStack('vampire');
    $stack[$this->GetVoter()->uname][] = $uname;
    $this->SetStack($stack, 'vampire');
  }

  //対吸血処理
  protected function InfectVampire(User $user) {
    $this->AddSuccess($this->GetID(), 'vampire_kill');
  }

  //吸血死＆吸血処理
  function VampireKill() {
    foreach ($this->GetStack('vampire_kill') as $id => $flag) { //吸血死処理
      DB::$USER->Kill($id, 'VAMPIRE_KILLED');
    }

    foreach ($this->GetStack('vampire') as $uname => $stack) {
      $filter = RoleManager::LoadMain(DB::$USER->ByUname($uname));
      foreach ($stack as $target_uname) $filter->Infect(DB::$USER->ByUname($target_uname));
    }
  }

  //吸血処理
  function Infect(User $user) { $user->AddRole($this->GetActor()->GetID('infected')); }
}
