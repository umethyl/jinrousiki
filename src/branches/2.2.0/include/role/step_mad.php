<?php
/*
  ◆家鳴 (step_mad)
  ○仕様
*/
class Role_step_mad extends Role {
  public $action     = 'STEP_DO';
  public $not_action = 'STEP_NOT_DO';


  function OutputAction() {
    RoleHTML::OutputVote('step-do', 'step_do', $this->action, $this->not_action);
  }

  function IsVoteCheckbox(User $user, $live) { return true; }

  function GetVoteCheckboxHeader() { return '<input type="checkbox" name="target_no[]"'; }

  function VoteNight() {
    $stack = $this->GetVoteNightTarget();
    //Text::p($stack);
    sort($stack);

    $id  = array_shift($stack);
    $max = count(DB::$USER->rows);
    $vector = null;
    $count  = 0;
    $root_list = array($id);
    while (count($stack) > 0) {
      $chain = $this->GetChain($id, $max);
      $point = array_intersect($chain, $stack);
      if (count($point) != 1) return '通り道が一本に繋がっていません';

      $new_vector = array_shift(array_keys($point));
      if ($new_vector != $vector) {
	if ($count++ > 0) return '通り道は直線にしてください';
	$vector = $new_vector;
      }

      $id = array_shift($point);
      $root_list[] = $id;
      unset($stack[array_search($id, $stack)]);
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
  function Step(array $list) {
    $stack = array();
    foreach ($list as $id) {
      if (DB::$USER->IsVirtualLive($id)) $stack[] = $id;
    }
    if (count($stack) < 1) return true;
    sort($stack);
    return DB::$ROOM->ResultDead(implode(' ', $stack), 'STEP');
  }
}
