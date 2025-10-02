<?php
/*
  ◆響狼 (step_wolf)
  ○仕様
*/
RoleManager::LoadFile('wolf');
class Role_step_wolf extends Role_wolf {
  public $mix_in = array('step_mage');
  public $action     = 'STEP_WOLF_EAT';
  public $add_action = 'SILENT_WOLF_EAT';
  public $submit     = 'wolf_eat';

  protected function SetVoteNightFilter() {
    if (DB::$ROOM->IsEvent('no_step') || $this->IsDummyBoy() || ! $this->GetActor()->IsActive()) {
      $this->SetStack(null, 'add_action');
    }
  }

  public function IsVoteCheckbox(User $user, $live) {
    return ! $this->IsActor($user);
  }

  protected function GetVoteCheckboxHeader() {
    return RoleHTML::GetVoteCheckboxHeader('checkbox');
  }

  public function CheckVoteNightTarget(array $list) {
    $root_list = array();
    if ($this->IsDummyBoy()) { //身代わり君襲撃モード
      $id = array_shift($list);
      if (! DB::$USER->ByID($id)->IsDummyBoy()) { //身代わり君判定
	if (DB::$ROOM->IsQuiz()) {
	  return VoteRoleMessage::TARGET_QUIZ;
	} else {
	  return VoteRoleMessage::TARGET_ONLY_DUMMY_BOY;
	}
      }
      if (count($list) > 0) return VoteRoleMessage::UNCHAINED_ROUTE;
      $root_list[] = $id;
    } else {
      $id     = $this->GetActor()->id;
      $max    = DB::$USER->GetUserCount();
      $vector = null;
      $count  = 0;
      do {
	$chain = $this->GetChain($id, $max);
	$point = array_intersect($chain, $list);
	if (count($point) != 1) return VoteRoleMessage::UNCHAINED_ROUTE;

	$new_vector = array_shift(array_keys($point));
	if ($new_vector != $vector) {
	  if ($count++ > 1) return VoteRoleMessage::INVALID_VECTOR;
	  $vector = $new_vector;
	}

	$id = array_shift($point);
	$root_list[] = $id;
	unset($list[array_search($id, $list)]);
      } while (count($list) > 0);
    }
    if (count($root_list) < 1) return VoteRoleMessage::UNCHAINED_SELF;

    $target = DB::$USER->ByID($id);
    $live   = DB::$USER->IsVirtualLive($target->id); //生死判定は仮想を使う
    $str    = parent::IgnoreVoteNight($target, $live);
    if (! is_null($str)) return $str;

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
}
