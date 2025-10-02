<?php
/*
  ◆風神 (step_assassin)
  ○仕様
  ・暗殺：熱病付加
*/
RoleLoader::LoadFile('assassin');
class Role_step_assassin extends Role_assassin {
  public $mix_in = ['step_mad'];
  public $action = VoteAction::STEP_ASSASSIN;
  public $submit = VoteAction::ASSASSIN;

  protected function IsVoteNightCheckboxLive($live) {
    return true;
  }

  protected function DisableVoteNightCheckboxSelf() {
    return false;
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function ValidateVoteNightTargetList(array $list) {
    return $this->ValidateStepVoteNightTargetList($list);
  }

  //範囲暗殺
  public function SetStepAssassin(array $list) {
    $rate = max(1, 100 - (count($list) * 15));
    //Text::p($rate, '◆Rate');
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsLive(true) && Lottery::Percent($rate)) {
	$this->SetAssassin($user);
      }
    }
  }

  protected function IsAssassinKill() {
    return false;
  }

  protected function AssassinAction(User $user) {
    $user->AddDoom(1, 'febris');
  }
}
