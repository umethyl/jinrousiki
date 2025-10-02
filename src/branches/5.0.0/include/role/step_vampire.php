<?php
/*
  ◆文武王 (step_vampire)
  ○仕様
  ・処刑投票：感染者付加 (確率)
*/
RoleLoader::LoadFile('vampire');
class Role_step_vampire extends Role_vampire {
  public $mix_in = ['step_mage'];
  public $action = VoteAction::STEP_VAMPIRE;
  public $submit = VoteAction::VAMPIRE;

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  public function VoteKillAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoteKill($uname) || false === Lottery::Percent(30)) {
	continue;
      }

      $user = DB::$USER->ByRealUname($target_uname);
      if ($user->IsDead(true)) {
	continue;
      }

      //吸血鬼判定
      if ($user->IsMainGroup(CampGroup::VAMPIRE) ||
	  (RoleUser::IsDelayCopy($user) && $user->IsCamp(Camp::VAMPIRE))) {
	continue;
      }
      $user->AddRole(DB::$USER->ByUname($uname)->GetID('infected'));
    }
  }

  protected function IsVoteNightCheckboxLive($live) {
    return true;
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function ValidateVoteNightTargetList(array $list) {
    return $this->ValidateStepVoteNightTargetList($list);
  }
}
