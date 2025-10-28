<?php
/*
  ◆響狼 (step_wolf)
  ○仕様
*/
RoleLoader::LoadFile('wolf');
class Role_step_wolf extends Role_wolf {
  public $mix_in = ['step_mage'];
  public $action     = VoteAction::STEP_WOLF;
  public $add_action = VoteAction::SILENT_WOLF;
  public $submit     = VoteAction::WOLF;

  protected function DisableAddAction() {
    return DB::$ROOM->IsEvent('no_step') || $this->FixDummyBoy() ||
      false === $this->IsActorActive();
  }

  protected function IsVoteNightCheckboxLive($live) {
    return true;
  }

  protected function IsVoteNightCheckboxFilter(User $user) {
    return true;
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function ValidateVoteNightTargetList(array $list) {
    //-- 経路判定 --//
    $root_list = [];
    if ($this->FixDummyBoy()) { //身代わり君襲撃固定モード
      $id = array_shift($list);
      if (false === DB::$USER->ByID($id)->IsDummyBoy()) { //身代わり君判定
	if (DB::$ROOM->IsQuiz()) {
	  throw new UnexpectedValueException(VoteRoleMessage::TARGET_QUIZ);
	} else {
	  throw new UnexpectedValueException(VoteRoleMessage::TARGET_ONLY_DUMMY_BOY);
	}
      }

      if (count($list) > 0) {
	throw new UnexpectedValueException(VoteRoleMessage::UNCHAINED_ROUTE);
      }
      $root_list[] = $id;
    } else {
      $id     = $this->GetID();
      $max    = DB::$USER->Count();
      $vector = null;
      $count  = 0;
      do {
	$chain = Position::GetChain($id, $max);
	$point = array_intersect($chain, $list);
	if (count($point) != 1) {
	  throw new UnexpectedValueException(VoteRoleMessage::UNCHAINED_ROUTE);
	}

	$new_vector = ArrayFilter::PickKey($point);
	if ($new_vector != $vector) {
	  if ($count++ > 1) {
	    throw new UnexpectedValueException(VoteRoleMessage::INVALID_VECTOR);
	  }
	  $vector = $new_vector;
	}

	$id = array_shift($point);
	$root_list[] = $id;
	ArrayFilter::Delete($list, $id);
      } while (count($list) > 0);
    }

    if (count($root_list) < 1) {
      throw new UnexpectedValueException(VoteRoleMessage::UNCHAINED_SELF);
    }

    //-- 対象者判定 --//
    $target = DB::$USER->ByID($id);
    $live   = DB::$USER->IsVirtualLive($target->id); //生死判定は仮想を使う
    $this->ValidateVoteNightTarget($target, $live);

    //-- 投票情報登録 --//
    $target_stack = [];
    $handle_stack = [];
    foreach ($root_list as $id) { //投票順に意味があるので sort しない
      //対象者のみ憑依追跡する
      $target_stack[] = ($id == $target->id) ? DB::$USER->ByReal($id)->id : $id;
      $handle_stack[] = DB::$USER->ByID($id)->handle_name;
    }

    $this->SetStack(ArrayFilter::Concat($target_stack), RequestDataVote::TARGET);
    $this->SetStack(ArrayFilter::Concat($handle_stack), 'target_handle');
  }

  protected function IsInvalidVoteNightTargetLive($live) {
    return false === $live;
  }

  protected function GetWolfTargetID($id) {
    return Text::CutPop($id, ' '); //響狼は最終投票者
  }
}
