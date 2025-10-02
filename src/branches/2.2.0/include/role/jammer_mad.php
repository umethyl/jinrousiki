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
  final function SetJammer(User $user) {
    $class = $this->GetClass($method = 'IsJammer');
    if ($class->$method($user)) $this->AddStack($user->id, 'jammer');
  }

  //妨害対象セット成立判定
  function IsJammer(User $user) {
    $filter_list = RoleManager::LoadFilter('guard_curse'); //厄払い・妨害無効フィルタ
    if ($user->IsCursed() || in_array($user->id, $this->GetStack('voodoo'))) { //呪返し判定
      $actor = $this->GetActor();
      foreach ($filter_list as $filter) { //厄払い判定
	if ($filter->IsGuard($actor->id)) return false;
      }
      DB::$USER->Kill($actor->id, 'CURSED');
      return false;
    }

    foreach ($filter_list as $filter) { //妨害無効判定
      if ($filter->IsGuard($user->id)) return false;
    }
    return true;
  }
}
