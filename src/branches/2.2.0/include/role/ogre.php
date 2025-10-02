<?php
/*
  ◆鬼 (ogre)
  ○仕様
  ・勝利：生存 + 人狼系の生存
  ・人狼襲撃：確率無効
*/
class Role_ogre extends Role {
  public $action     = 'OGRE_DO';
  public $not_action = 'OGRE_NOT_DO';
  public $resist_rate  = 30;
  public $reduce_rate  =  5;
  public $reflect_rate = 30;

  function OutputAction() {
    RoleHTML::OutputVote('ogre-do', 'ogre_do', $this->action, $this->not_action);
  }

  function IsVote() { return DB::$ROOM->date > 1; }

  function GetIgnoreMessage() { return '初日は人攫いできません'; }

  function ExistsActionFilter(array $list) {
    if (DB::$ROOM->IsEvent('force_assassin_do')) unset($list[$this->not_action]);
    return $list;
  }

  function SetVoteNightFilter() {
    if (DB::$ROOM->IsEvent('force_assassin_do')) $this->SetStack(null, 'not_action');
  }

  function Win($winner) {
    if ($this->IsDead()) return false;
    if ($winner == 'wolf') return true;
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLiveRoleGroup('wolf')) return true;
    }
    return false;
  }

  //人攫い情報セット
  function SetAssassin(User $user) {
    foreach (RoleManager::LoadFilter('trap') as $filter) { //罠判定
      if ($filter->DelayTrap($this->GetActor(), $user->id)) return;
    }
    foreach (RoleManager::LoadFilter('guard_assassin') as $filter) { //対暗殺護衛判定
      if ($filter->GuardAssassin($user->id)) return;
    }
    if ($user->IsDead(true) || $user->IsMainGroup('escaper')) return; //無効判定
    if ($user->IsReflectAssassin()) { //反射判定
      $this->AddSuccess($this->GetID(), 'ogre');
      return;
    }
    if ($this->IgnoreAssassin($user)) return; //個別無効判定

    //人攫い成功判定
    $count = (int)$this->GetActor()->GetMainRoleTarget();
    $event = $this->GetEvent();
    $rate  = is_null($event) ? ceil(100 * pow($this->GetReduceRate(), $count)) : $event;
    if (! Lottery::Percent($rate)) return; //成功判定
    $this->Assassin($user);

    if (DB::$ROOM->IsEvent('full_ogre')) return; //成功回数更新処理 (朧月ならスキップ)
    $role = $this->role;
    if ($count > 0) $role .= sprintf('[%d]', $count);
    $this->GetActor()->ReplaceRole($role, sprintf('%s[%d]', $this->role, $count + 1));
  }

  //人攫い失敗判定
  protected function IgnoreAssassin(User $user) { return false; }

  //天候情報取得
  protected function GetEvent() {
    return DB::$ROOM->IsEvent('full_ogre') ? 100 : (DB::$ROOM->IsEvent('seal_ogre') ? 0 : null);
  }

  //人攫い成功減衰率取得
  protected function GetReduceRate() { return 1 / $this->reduce_rate; }

  //人攫い
  protected function Assassin(User $user) { $this->AddSuccess($user->id, 'ogre'); }

  //人攫い死
  function AssassinKill() {
    foreach ($this->GetStack() as $id => $flag) DB::$USER->Kill($id, 'OGRE_KILLED');
  }

  //人狼襲撃耐性判定
  final function WolfEatResist() { return Lottery::Percent($this->GetResistRate()); }

  //人狼襲撃耐性率取得
  protected function GetResistRate() {
    return is_null($event = $this->GetEvent()) ? $this->resist_rate : $event;
  }
}
