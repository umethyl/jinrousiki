<?php
/*
  ◆山立 (step_guard)
  ○仕様
  ・護衛失敗：制限なし
*/
RoleManager::LoadFile('guard');
class Role_step_guard extends Role_guard {
  public $mix_in = 'step_mage';
  public $action = 'STEP_GUARD_DO';
  public $submit = 'guard_do';

  public function IsVoteCheckbox(User $user, $live) {
    return ! $this->IsActor($user);
  }

  protected function GetVoteCheckboxHeader() {
    return '<input type="checkbox" name="target_no[]"';
  }

  public function CheckVoteNightTarget(array $list) {
    return $this->filter->CheckVoteNightTarget($list);
  }

  public function IgnoreGuard() {
    return null;
  }
}
