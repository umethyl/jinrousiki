<?php
/*
  ◆文武王 (step_vampire)
  ○仕様
  ・処刑投票：感染者付加 (確率)
*/
RoleManager::LoadFile('vampire');
class Role_step_vampire extends Role_vampire {
  public $action = 'STEP_VAMPIRE_DO';
  public $submit = 'vampire_do';

  function IsVoteCheckbox(User $user, $live) { return ! $this->IsActor($user); }

  function GetVoteCheckboxHeader() { return '<input type="checkbox" name="target_no[]"'; }

  function VoteNight() {
    $stack = $this->GetVoteNightTarget();
    //Text::p($stack);

    $id  = $this->GetActor()->id;
    $max = count(DB::$USER->rows);
    $vector = null;
    $count  = 0;
    $root_list = array();
    do {
      $chain = $this->GetChain($id, $max);
      $point = array_intersect($chain, $stack);
      if (count($point) != 1) return '通り道が一本に繋がっていません';

      $new_vector = array_shift(array_keys($point));
      if ($new_vector != $vector) {
	if ($count++ > 1) return '方向転換は一回まで';
	$vector = $new_vector;
      }

      $id = array_shift($point);
      $root_list[] = $id;
      unset($stack[array_search($id, $stack)]);
    } while (count($stack) > 0);
    if (count($root_list) < 1) return '通り道が自分と繋がっていません';

    $target = DB::$USER->ByID($id);
    if ($this->IsActor($target) || ! DB::$USER->IsVirtualLive($id)) { //例外判定
      return '自分・死者には投票できません';
    }

    $target_stack = array();
    $handle_stack = array();
    foreach ($root_list as $id) { //投票順に意味があるので sort しない
      //対象者のみ憑依追跡する
      $target_stack[] = $id == $target->id ? DB::$USER->ByReal($id)->id : $id;
      $handle_stack[] = DB::$USER->ByID($id)->handle_name;
    }

    $this->SetStack(implode(' ', $target_stack), 'target_no');
    $this->SetStack(implode(' ', $handle_stack), 'target_handle');
    return null;
  }

  //足音処理
  function Step(array $list) {
    array_pop($list); //最後尾は対象者なので除く
    $stack = array();
    foreach ($list as $id) {
      if (DB::$USER->IsVirtualLive($id)) $stack[] = $id;
    }
    if (count($stack) < 1) return true;
    sort($stack);
    return DB::$ROOM->ResultDead(implode(' ', $stack), 'STEP');
  }

  function SetVoteDay($uname) {
    $this->InitStack();
    if ($this->IsRealActor()) $this->AddStackName($uname);
  }

  function VoteAction() {
    foreach ($this->GetStack() as $uname => $target_uname) {
      if ($this->IsVoted($uname) || ! Lottery::Percent(30)) continue;
      $user = DB::$USER->ByRealUname($target_uname);
      if ($user->IsDead(true)) continue;

      //吸血鬼判定
      if ($user->IsMainGroup('vampire') ||
	  ($user->IsRole('soul_mania', 'dummy_mania') && $user->IsCamp('vampire'))) {
	continue;
      }
      $user->AddRole(DB::$USER->ByUname($uname)->GetID('infected'));
    }
  }
}
