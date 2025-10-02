<?php
/*
  ◆呪術師 (voodoo_mad)
  ○仕様
*/
class Role_voodoo_mad extends Role {
  public $action = 'VOODOO_MAD_DO';
  public $submit = 'voodoo_do';

  function OutputAction() { RoleHTML::OutputVote('wolf-eat', $this->submit, $this->action); }

  //呪術対象セット
  final function SetVoodoo(User $user) {
    if ($user->IsCursed()) { //呪返し判定
      $actor = $this->GetActor();
      foreach ($this->GetGuardCurse() as $filter) { //厄払い判定
	if ($filter->IsGuard($actor->id)) return false;
      }
      DB::$USER->Kill($actor->id, 'CURSED');
      return false;
    }

    if (in_array($user->id, $this->GetStack('voodoo_killer'))) { //陰陽師の解呪判定
      $this->AddSuccess($user->id, 'voodoo_killer_success');
    } else {
      $this->AddStack($user->id, 'voodoo');
    }
  }

  //呪術能力者の呪返し処理
  final function VoodooToVoodoo() {
    $stack       = $this->GetStack('voodoo');
    $count_list  = array_count_values($stack);
    $filter_list = $this->GetGuardCurse();
    foreach ($stack as $id => $target_id) {
      if ($count_list[$target_id] < 2) continue;
      $user = DB::$USER->ByID($id);
      foreach ($filter_list as $filter) { //厄払い判定
	if ($filter->IsGuard($user->id)) continue 2;
      }
      DB::$USER->Kill($user->id, 'CURSED');
    }
  }

  //厄払いフィルタ取得
  protected function GetGuardCurse() {
    if (! is_array($stack = $this->GetStack($type = 'guard_curse'))) {
      $stack = RoleManager::LoadFilter($type);
      $this->SetStack($stack, $type);
    }
    return $stack;
  }
}
