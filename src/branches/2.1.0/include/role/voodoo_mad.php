<?php
/*
  ◆呪術師 (voodoo_mad)
  ○仕様
*/
class Role_voodoo_mad extends Role {
  public $action = 'VOODOO_MAD_DO';
  public $submit = 'voodoo_do';

  function OutputAction() {
    RoleHTML::OutputVote('wolf-eat', $this->submit, $this->action);
  }

  //呪術対象セット
  function SetVoodoo(User $user) {
    if ($user->IsCursed()) { //呪返し判定
      $actor = $this->GetActor();
      foreach ($this->GetGuardCurse() as $filter) { //厄払い判定
	if ($filter->IsGuard($actor->uname)) return false;
      }
      DB::$USER->Kill($actor->user_no, 'CURSED');
      return false;
    }
    if (in_array($user->uname, $this->GetStack('voodoo_killer'))) { //陰陽師の解呪判定
      $this->AddSuccess($user->uname, 'voodoo_killer_success');
    }
    else {
      $this->AddStack($user->uname, 'voodoo');
    }
  }

  //呪術能力者の呪返し処理
  function VoodooToVoodoo() {
    $stack = $this->GetStack('voodoo');
    $count_list  = array_count_values($stack);
    $filter_list = $this->GetGuardCurse();
    foreach ($stack as $uname => $target_uname) {
      if ($count_list[$target_uname] > 1) {
	$user = DB::$USER->ByUname($uname);
	foreach ($filter_list as $filter) { //厄払い判定
	  if ($filter->IsGuard($user->uname)) continue 2;
	}
	DB::$USER->Kill($user->user_no, 'CURSED');
      }
    }
  }

  //厄払いフィルタ取得
  protected function GetGuardCurse() {
    if (! is_array($stack = $this->GetStack($data = 'guard_curse'))) {
      $stack = RoleManager::LoadFilter($data);
      $this->SetStack($stack, $data);
    }
    return $stack;
  }
}
