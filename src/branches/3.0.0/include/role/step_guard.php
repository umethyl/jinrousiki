<?php
/*
  ◆山立 (step_guard)
  ○仕様
  ・護衛失敗：制限なし
*/
RoleManager::LoadFile('guard');
class Role_step_guard extends Role_guard {
  public $mix_in = array('step_mage');
  public $action = 'STEP_GUARD_DO';
  public $submit = 'guard_do';

  public function IsVoteCheckbox(User $user, $live) {
    return ! $this->IsActor($user);
  }

  protected function GetVoteCheckboxHeader() {
    return RoleHTML::GetVoteCheckboxHeader('checkbox');
  }

  public function CheckVoteNightTarget(array $list) {
    return $this->CheckStepVoteNightTarget($list);
  }

  public function IgnoreGuard() {
    return null;
  }
}
