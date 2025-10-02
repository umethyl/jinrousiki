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
  public $action_date_type = 'after';
  public $resist_rate  = 30;
  public $reduce_base  =  1;
  public $reduce_rate  =  5;
  public $reflect_rate = 30;

  public function OutputAction() {
    RoleHTML::OutputVote('ogre-do', 'ogre_do', $this->action, $this->not_action);
  }

  protected function SetVoteNightFilter() {
    if (DB::$ROOM->IsEvent('force_assassin_do')) $this->SetStack(null, 'not_action');
  }

  protected function ExistsActionFilter(array $list) {
    if (DB::$ROOM->IsEvent('force_assassin_do')) unset($list[$this->not_action]);
    return $list;
  }

  public function Win($winner) {
    if ($this->IsDead()) return false;
    if ($winner == 'wolf') return true;
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLive() && $user->IsMainGroup('wolf')) return true;
    }
    return false;
  }

  //人攫い情報セット
  public function SetAssassin(User $user) {
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
    if (is_null($event)) {
      $rate = ceil(100 * pow($this->reduce_base / $this->reduce_rate, $count));
    } else {
      $rate = $event;
    }
    //Text::p($rate, '◆Assassin Rate [ogre]');
    if (! Lottery::Percent($rate)) return; //成功判定
    $this->Assassin($user);

    if (DB::$ROOM->IsEvent('full_ogre')) return; //成功回数更新処理 (朧月ならスキップ)
    $role = $this->role;
    if ($count > 0) $role .= sprintf('[%d]', $count);
    $this->GetActor()->ReplaceRole($role, sprintf('%s[%d]', $this->role, $count + 1));
  }

  //人攫い失敗判定
  protected function IgnoreAssassin(User $user) {
    return false;
  }

  //天候情報取得
  final protected function GetEvent() {
    return DB::$ROOM->IsEvent('full_ogre') ? 100 : (DB::$ROOM->IsEvent('seal_ogre') ? 0 : null);
  }

  //人攫い
  protected function Assassin(User $user) {
    $this->AddSuccess($user->id, 'ogre');
  }

  //人攫い死
  final public function AssassinKill() {
    foreach ($this->GetStack() as $id => $flag) {
      DB::$USER->Kill($id, 'OGRE_KILLED');
    }
  }

  //人狼襲撃耐性判定
  final public function WolfEatResist() {
    $rate = is_null($event = $this->GetEvent()) ? $this->resist_rate : $event;
    //Text::p($rate, '◆Resist Rate [ogre]');
    return Lottery::Percent($rate);
  }
}
