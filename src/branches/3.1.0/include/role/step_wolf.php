<?php
/*
  ◆響狼 (step_wolf)
  ○仕様
*/
RoleLoader::LoadFile('wolf');
class Role_step_wolf extends Role_wolf {
  public $mix_in = array('step_mage');
  public $action     = VoteAction::STEP_WOLF;
  public $add_action = VoteAction::SILENT_WOLF;
  public $submit     = VoteAction::WOLF;

  protected function IgnoreAddAction() {
    return DB::$ROOM->IsEvent('no_step') || $this->IsFixDummyBoy() || ! $this->IsActorActive();
  }

  protected function IsVoteCheckboxLive($live) {
    return true;
  }

  protected function IsVoteCheckboxFilter(User $user) {
    return true;
  }

  protected function GetVoteCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  public function CheckVoteNightTarget(array $list) {
    $root_list = array();
    if ($this->IsFixDummyBoy()) { //身代わり君襲撃固定モード
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
      $id     = $this->GetID();
      $max    = DB::$USER->Count();
      $vector = null;
      $count  = 0;
      do {
	$chain = Position::GetChain($id, $max);
	$point = array_intersect($chain, $list);
	if (count($point) != 1) return VoteRoleMessage::UNCHAINED_ROUTE;

	$new_vector = ArrayFilter::PickKey($point);
	if ($new_vector != $vector) {
	  if ($count++ > 1) return VoteRoleMessage::INVALID_VECTOR;
	  $vector = $new_vector;
	}

	$id = array_shift($point);
	$root_list[] = $id;
	ArrayFilter::Delete($list, $id);
      } while (count($list) > 0);
    }
    if (count($root_list) < 1) return VoteRoleMessage::UNCHAINED_SELF;

    $target = DB::$USER->ByID($id);
    $live   = DB::$USER->IsVirtualLive($target->id); //生死判定は仮想を使う
    $str    = $this->IgnoreVoteNight($target, $live);
    if (! is_null($str)) return $str;

    $target_stack = array();
    $handle_stack = array();
    foreach ($root_list as $id) { //投票順に意味があるので sort しない
      //対象者のみ憑依追跡する
      $target_stack[] = $id == $target->id ? DB::$USER->ByReal($id)->id : $id;
      $handle_stack[] = DB::$USER->ByID($id)->handle_name;
    }

    $this->SetStack(ArrayFilter::Concat($target_stack), RequestDataVote::TARGET);
    $this->SetStack(ArrayFilter::Concat($handle_stack), 'target_handle');
    return null;
  }

  protected function IgnoreVoteNightLive($live) {
    return ! $live;
  }
}
