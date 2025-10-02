<?php
/*
  ◆響狐 (step_fox)
  ○仕様
*/
RoleManager::LoadFile('fox');
class Role_step_fox extends Role_fox {
  public $mix_in = 'step_mad';
  public $action     = 'STEP_DO';
  public $not_action = 'STEP_NOT_DO';

  public function OutputAction() {
    RoleHTML::OutputVote('step-do', 'step_do', $this->action, $this->not_action);
  }

  public function IsVoteCheckbox(User $user, $live) {
    return true;
  }

  protected function GetVoteCheckboxHeader() {
    return '<input type="checkbox" name="target_no[]"';
  }

  public function CheckVoteNightTarget(array $list) {
    return $this->filter->CheckVoteNightTarget($list);
  }
}
