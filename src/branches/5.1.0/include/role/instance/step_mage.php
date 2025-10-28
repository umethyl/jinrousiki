<?php
/*
  ◆審神者 (step_mage)
  ○仕様
*/
RoleLoader::LoadFile('mage');
class Role_step_mage extends Role_mage {
  public $action = VoteAction::STEP_MAGE;
  public $submit = VoteAction::MAGE;

  protected function IsVoteNightCheckboxLive($live) {
    return true;
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function ValidateVoteNightTargetList(array $list) {
    return $this->ValidateStepVoteNightTargetList($list);
  }

  //複合投票型夜投票無効判定 (足音能力者用)
  protected function ValidateStepVoteNightTargetList(array $list) {
    //-- 経路判定 --//
    $id     = $this->GetID();
    $max    = DB::$USER->Count();
    $vector = null;
    $count  = 0;
    $stack  = [];
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
      $stack[] = $id;
      ArrayFilter::Delete($list, $id);
    } while (count($list) > 0);

    if (count($stack) < 1) {
      throw new UnexpectedValueException(VoteRoleMessage::UNCHAINED_SELF);
    }

    //-- 対象者判定 --//
    $target = DB::$USER->ByID($id);
    if (false === DB::$USER->IsVirtualLive($id)) {
      throw new UnexpectedValueException(VoteRoleMessage::TARGET_DEAD);
    }
    if ($this->IsActor($target)) {
      throw new UnexpectedValueException(VoteRoleMessage::TARGET_MYSELF);
    }

    //-- 投票情報登録 --//
    $target_stack = [];
    $handle_stack = [];
    foreach ($stack as $id) { //投票順に意味があるので sort しない
      //対象者のみ憑依追跡する
      $target_stack[] = ($id == $target->id) ? DB::$USER->ByReal($id)->id : $id;
      $handle_stack[] = DB::$USER->ByID($id)->handle_name;
    }

    $this->SetStack(ArrayFilter::Concat($target_stack), RequestDataVote::TARGET);
    $this->SetStack(ArrayFilter::Concat($handle_stack), 'target_handle');
  }

  //足音処理
  public function Step(array $list) {
    if ($this->IgnoreStep()) {
      return false;
    }

    array_pop($list); //最後尾は対象者なので除く
    $stack = [];
    foreach ($list as $id) {
      if (DB::$USER->IsVirtualLive($id)) {
	$stack[] = $id;
      }
    }
    if (count($stack) < 1) {
      return true;
    }

    sort($stack);
    return DB::$ROOM->StoreDead(ArrayFilter::Concat($stack), DeadReason::STEP);
  }

  //足音無効判定
  final protected function IgnoreStep() {
    return $this->GetActor()->IsRole('levitation');
  }
}
