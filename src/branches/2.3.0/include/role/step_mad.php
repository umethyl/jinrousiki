<?php
/*
  ◆家鳴 (step_mad)
  ○仕様
*/
class Role_step_mad extends Role {
  public $mix_in = 'step_mage';
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
    sort($list);

    $id  = array_shift($list);
    $max = count(DB::$USER->rows);
    $vector = null;
    $count  = 0;
    $root_list = array($id);
    while (count($list) > 0) {
      $chain = $this->GetChain($id, $max);
      $point = array_intersect($chain, $list);
      if (count($point) != 1) return VoteRoleMessage::UNCHAINED_ROUTE;

      $new_vector = array_shift(array_keys($point));
      if ($new_vector != $vector) {
	if ($count++ > 0) return VoteRoleMessage::INVALID_ROUTE;
	$vector = $new_vector;
      }

      $id = array_shift($point);
      $root_list[] = $id;
      unset($list[array_search($id, $list)]);
    }

    $target_stack = array();
    $handle_stack = array();
    foreach ($root_list as $id) {
      $target_stack[] = $id;
      $handle_stack[] = DB::$USER->ByID($id)->handle_name;
    }

    $this->SetStack(implode(' ', $target_stack), 'target_no');
    $this->SetStack(implode(' ', $handle_stack), 'target_handle');
    return null;
  }

  //足音処理
  public function Step(array $list) {
    $stack = array();
    foreach ($list as $id) {
      if (DB::$USER->IsVirtualLive($id)) $stack[] = $id;
    }
    if (count($stack) < 1) return true;
    sort($stack);
    return DB::$ROOM->ResultDead(implode(' ', $stack), 'STEP');
  }
}
