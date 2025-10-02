<?php
/*
  ◆雷神 (step_scanner)
  ○仕様
  ・追加役職：なし
  ・投票結果：なし
  ・投票：2日目以降
*/
RoleManager::LoadFile('mind_scanner');
class Role_step_scanner extends Role_mind_scanner {
  public $mix_in = array('step_mad', 'step_mage');
  public $action = 'STEP_SCANNER_DO';
  public $action_date_type = 'after';
  public $mind_role = null;

  public function IsVoteCheckbox(User $user, $live) {
    return true;
  }

  protected function GetVoteCheckboxHeader() {
    return RoleHTML::GetVoteCheckboxHeader('checkbox');
  }

  public function CheckVoteNightTarget(array $list) {
    return $this->CheckStepVoteNightTarget($list);
  }

  //範囲透視
  public function StepScan(array $list) {
    //周辺ID取得
    //Text::p($list, '◆Target [Vote]');
    $max   = DB::$USER->GetUserCount();
    $stack = array();
    foreach ($list as $id) {
      $stack = array_merge($stack, array_values($this->GetChain($id, $max)));
    }

    $around_list = array();
    foreach ($stack as $id) {
      if (in_array($id, $list) || DB::$USER->ByID($id)->IsDead(true)) continue;
      $around_list[] = $id;
    }
    //Text::p($around_list, '◆Target [Around]');

    //確率判定
    $rate = min(80, count($around_list) * 8);
    //Text::p($rate, '◆Rate');
    if (Lottery::Percent(100 - $rate)) return;

    //会話能力者判定
    $step_flag = false;
    foreach ($around_list as $id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsDead(true)) continue;
      if ($user->IsCommon(true) || $user->IsWolf(true) || $user->IsFox(true) ||
	  $user->IsRole('mind_friend')) {
	$step_flag = true;
	break;
      }
    }
    if ($step_flag) $this->Step($list);
  }
}
