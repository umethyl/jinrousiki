<?php
/*
  ◆月兎 (jammer_mad)
  ○仕様
*/
class Role_jammer_mad extends Role {
  public $action = 'JAMMER_MAD_DO';
  public $submit = 'jammer_do';

  function OutputAction() {
    RoleHTML::OutputVote('wolf-eat', $this->submit, $this->action);
  }

  //妨害対象セット
  function SetJammer(User $user) {
    if ($this->IsJammer($user)) $this->AddStack($user->uname, 'jammer');
  }

  //妨害対象セット成立判定 (Mixin あり)
  function IsJammer(User $user) {
    $filter_list = RoleManager::LoadFilter('guard_curse'); //厄払い・妨害無効フィルタ
    if ($user->IsCursed() || in_array($user->uname, $this->GetStack('voodoo'))) { //呪返し判定
      $actor = $this->GetActor();
      foreach ($filter_list as $filter) { //厄払い判定
	if ($filter->IsGuard($actor->uname)) return false;
      }
      DB::$USER->Kill($actor->user_no, 'CURSED');
      return false;
    }

    foreach ($filter_list as $filter) { //妨害無効判定
      if ($filter->IsGuard($user->uname)) return false;
    }
    return true;
  }
}
