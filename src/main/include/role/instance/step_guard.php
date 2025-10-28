<?php
/*
  ◆山立 (step_guard)
  ○仕様
  ・護衛制限：なし
*/
RoleLoader::LoadFile('guard');
class Role_step_guard extends Role_guard {
  public $mix_in = ['step_mage'];
  public $action = VoteAction::STEP_GUARD;
  public $submit = VoteAction::GUARD;

  protected function IsVoteNightCheckboxLive($live) {
    return true;
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function ValidateVoteNightTargetList(array $list) {
    return $this->ValidateStepVoteNightTargetList($list);
  }

  public function UnlimitedGuard() {
    return true;
  }
}
