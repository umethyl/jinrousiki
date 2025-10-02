<?php
/*
  ◆山立 (step_guard)
  ○仕様
  ・護衛失敗：制限なし
*/
RoleLoader::LoadFile('guard');
class Role_step_guard extends Role_guard {
  public $mix_in = array('step_mage');
  public $action = VoteAction::STEP_GUARD;
  public $submit = VoteAction::GUARD;

  protected function IsVoteCheckboxLive($live) {
    return true;
  }

  protected function GetVoteCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  public function CheckVoteNightTarget(array $list) {
    return $this->CheckStepVoteNightTarget($list);
  }

  public function IgnoreGuard(User $user) {
    return null;
  }
}
