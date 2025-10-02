<?php
/*
  ◆響狼 (step_wolf)
  ○仕様
*/
RoleManager::LoadFile('wolf');
class Role_step_wolf extends Role_wolf {
  public $action     = 'STEP_WOLF_EAT';
  public $add_action = 'SILENT_WOLF_EAT';
  public $submit     = 'wolf_eat';

  function IsVoteCheckbox(User $user, $live) { return ! $this->IsActor($user); }

  function GetVoteCheckboxHeader() { return '<input type="checkbox" name="target_no[]"'; }

  function SetVoteNightFilter() {
    if (DB::$ROOM->IsEvent('no_step') || $this->IsDummyBoy() || ! $this->GetActor()->IsActive()) {
      $this->SetStack(null, 'add_action');
    }
  }

  function VoteNight() {
    $stack = $this->GetVoteNightTarget();
    //Text::p($stack);

    $root_list = array();
    if ($this->IsDummyBoy()) { //身代わり君襲撃モード
      $id = array_shift($stack);
      if (! DB::$USER->ByID($id)->IsDummyBoy()) { //身代わり君判定
	return DB::$ROOM->IsQuiz() ? 'クイズ村では GM 以外に投票できません' :
	  '身代わり君使用の場合は、身代わり君以外に投票できません';
      }
      if (count($stack) > 0) return '通り道が一本に繋がっていません';
      $root_list[] = $id;
    } else {
      $id  = $this->GetActor()->id;
      $max = count(DB::$USER->rows);
      $vector = null;
      $count  = 0;
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
    }
    if (count($root_list) < 1) return '通り道が自分と繋がっていません';

    $target = DB::$USER->ByID($id);
    $live   = DB::$USER->IsVirtualLive($target->id); //仮想的な生死を判定
    if (! is_null($str = parent::IgnoreVoteNight($target, $live))) return $str;

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
}
