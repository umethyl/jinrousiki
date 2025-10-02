<?php
/*
  ◆風神 (step_assassin)
  ○仕様
  ・暗殺：熱病付加
*/
RoleLoader::LoadFile('assassin');
class Role_step_assassin extends Role_assassin {
  public $mix_in = array('step_mad');
  public $action = VoteAction::STEP_ASSASSIN;
  public $submit = VoteAction::ASSASSIN;

  protected function IsVoteCheckboxLive($live) {
    return true;
  }

  protected function IgnoreVoteCheckboxSelf() {
    return false;
  }

  protected function GetVoteCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  public function CheckVoteNightTarget(array $list) {
    return $this->CheckStepVoteNightTarget($list);
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
