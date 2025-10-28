<?php
/*
  ◆雷神 (step_scanner)
  ○仕様
  ・追加役職：なし
  ・投票結果：なし
  ・投票：2日目以降
*/
RoleLoader::LoadFile('mind_scanner');
class Role_step_scanner extends Role_mind_scanner {
  public $mix_in = ['step_mad', 'step_mage'];
  public $action = VoteAction::STEP_SCAN;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function GetMindRole() {
    return null;
  }

  protected function IsVoteNightCheckboxLive($live) {
    return true;
  }

  protected function DisableVoteNightCheckboxSelf() {
    return false;
  }

  protected function DisableVoteNightCheckboxDummyBoy() {
    return false;
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function ValidateVoteNightTargetList(array $list) {
    return $this->ValidateStepVoteNightTargetList($list);
  }

  //範囲透視
  public function StepMindScan(array $list) {
    //-- 罠判定 --//
    foreach (RoleLoader::LoadFilter('trap') as $filter) {
      foreach ($list as $id) {
	if ($filter->DelayTrap($this->GetActor(), $id)) {
	  return false;
	}
      }
    }

    //-- 足音無効判定 --//
    if ($this->IgnoreStep()) {
      return false;
    }

    //-- 周辺ID取得 --//
    //Text::p($list, '◆Target [Vote]');
    $max   = DB::$USER->Count();
    $stack = [];
    foreach ($list as $id) {
      ArrayFilter::AddMerge($stack, array_values(Position::GetChain($id, $max)));
    }

    $around_list = [];
    foreach ($stack as $id) {
      if (in_array($id, $list) || DB::$USER->ByID($id)->IsDead(true)) {
	continue;
      }
      $around_list[] = $id;
    }
    //Text::p($around_list, '◆Target [Around]');

    //-- 確率判定 --//
    $rate = min(80, count($around_list) * 10);
    //Text::p($rate, '◆Rate');
    if (Lottery::Percent(100 - $rate)) {
      return false;
    }

    //-- 会話能力者判定 --//
    foreach ($around_list as $id) {
      $user = DB::$USER->ByID($id);
      if (RoleUser::IsCommon($user) || RoleUser::IsWolf($user) || RoleUser::IsFox($user) ||
	  $user->IsRole('mind_friend')) {
	$this->Step($list); //足音処理
	return true;
      }
    }
  }
}
