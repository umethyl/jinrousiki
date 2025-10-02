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
  public $mix_in = 'step_mad';
  public $action    = 'STEP_SCANNER_DO';
  public $mind_role = null;

  public function IsVote() {
    return DB::$ROOM->date > 1;
  }

  protected function GetIgnoreMessage() {
    return VoteRoleMessage::IMPOSSIBLE_FIRST_DAY;
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

  //範囲透視
  public function StepScan(array $list) {
    //周辺ID取得
    //Text::p($list, '◆Target [Vote]');
    $max   = count(DB::$USER->rows);
    $stack = array();
    foreach ($list as $id) {
      $stack = array_merge($stack, array_values($this->filter->GetChain($id, $max)));
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
    if ($step_flag) $this->filter->Step($list);
  }
}
