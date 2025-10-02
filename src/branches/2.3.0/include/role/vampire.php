<?php
/*
  ◆吸血鬼 (vampire)
  ○仕様
  ・吸血：通常
  ・仲間表示：感染者・洗脳者
*/
class Role_vampire extends Role {
  public $action = 'VAMPIRE_DO';

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

  public function OutputAction() {
    RoleHTML::OutputVote('vampire-do', 'vampire_do', $this->action);
  }

  public function IsVote() {
    return DB::$ROOM->date > 1;
  }

  protected function GetIgnoreMessage() {
    return VoteRoleMessage::IMPOSSIBLE_FIRST_DAY;
  }

  //吸血対象セット
  final public function SetInfect(User $user) {
    $actor = $this->GetActor();
    $this->SetStack($actor, 'voter');
    foreach (RoleManager::LoadFilter('trap') as $filter) { //罠判定
      if ($filter->DelayTrap($actor, $user->id)) return;
    }
    foreach (array_keys($this->GetStack('escaper'), $actor->id) as $id) { //自己逃亡判定
      $this->SetInfectTarget($id);
    }
    foreach (array_keys($this->GetStack('escaper'), $user->id) as $id) { //逃亡巻き添え判定
      $this->SetInfectTarget($id);
    }
    if (RoleManager::GetClass('guard')->Guard($user)) return; //護衛判定
    if ($user->IsDead(true) || $user->IsMainGroup('escaper')) return; //スキップ判定

    //吸血リスト登録
    if ($user->IsMainGroup('vampire')) {
      RoleManager::LoadMain($user)->InfectVampire($actor); //吸血鬼襲撃
    }
    elseif ($user->IsDelayMania() && $user->IsCamp('vampire')) {
      if (! $user->IsAvoid()) $this->AddSuccess($user->id, 'vampire_kill'); //時間差コピー能力者
    }
    else {
      $this->SetInfectTarget($user->id);
    }
  }

  //吸血リスト登録
  final protected function SetInfectTarget($id) {
    $stack = $this->GetStack('vampire');
    $stack[$this->GetVoter()->id][] = $id;
    $this->SetStack($stack, 'vampire');
  }

  //対吸血処理
  protected function InfectVampire(User $user) {
    if (! $this->GetActor()->IsAvoid()) {
      $this->AddSuccess($this->GetID(), 'vampire_kill');
    }
  }

  //吸血死＆吸血処理
  final public function VampireKill() {
    foreach ($this->GetStack('vampire_kill') as $id => $flag) { //吸血死処理
      DB::$USER->Kill($id, 'VAMPIRE_KILLED');
    }

    foreach ($this->GetStack('vampire') as $id => $stack) {
      $filter = RoleManager::LoadMain(DB::$USER->ByID($id));
      foreach ($stack as $target_id) $filter->Infect(DB::$USER->ByID($target_id));
    }
  }

  //吸血処理
  protected function Infect(User $user) {
    $user->AddRole($this->GetActor()->GetID('infected'));
    $this->InfectAction($user);
  }

  //吸血追加処理
  protected function InfectAction(User $user) {}
}
