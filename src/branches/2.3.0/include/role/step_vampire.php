<?php
/*
  ◆文武王 (step_vampire)
  ○仕様
  ・処刑投票：感染者付加 (確率)
*/
RoleManager::LoadFile('vampire');
class Role_step_vampire extends Role_vampire {
  public $mix_in = 'step_mage';
  public $action = 'STEP_VAMPIRE_DO';
  public $submit = 'vampire_do';
  public $vote_day_type = 'init';

  public function IsVoteCheckbox(User $user, $live) {
    return ! $this->IsActor($user);
  }

  protected function GetVoteCheckboxHeader() {
    return '<input type="checkbox" name="target_no[]"';
  }

  public function CheckVoteNightTarget(array $list) {
    return $this->filter->CheckVoteNightTarget($list);
  }

  public function VoteAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname) || ! Lottery::Percent(30)) continue;
      $user = DB::$USER->ByRealUname($target_uname);
      if ($user->IsDead(true)) continue;

      //吸血鬼判定
      if ($user->IsMainGroup('vampire') || ($user->IsDelayMania() && $user->IsCamp('vampire'))) {
	continue;
      }
      $user->AddRole(DB::$USER->ByUname($uname)->GetID('infected'));
    }
  }
}
