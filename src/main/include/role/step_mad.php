<?php
/*
  ◆家鳴 (step_mad)
  ○仕様
*/
class Role_step_mad extends Role {
  public $mix_in = ['step_mage'];
  public $action     = VoteAction::STEP;
  public $not_action = VoteAction::NOT_STEP;

  public function OutputAction() {
    $str = RoleAbilityMessage::STEP;
    RoleHTML::OutputVoteNight(VoteCSS::STEP, $str, $this->action, $this->not_action);
  }

  protected function IsVoteNightCheckboxLive($live) {
    return true;
  }

  protected function DisableVoteNightCheckboxSelf() {
    return false;
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function ValidateVoteNightTargetList(array $list) {
    return $this->ValidateStepVoteNightTargetList($list);
  }

  protected function ValidateStepVoteNightTargetList(array $list) {
    //-- 経路判定 --//
    sort($list);

    $id     = array_shift($list);
    $max    = DB::$USER->Count();
    $vector = null;
    $count  = 0;
    $root_list = [$id];
    while (count($list) > 0) {
      $chain = Position::GetChain($id, $max);
      $point = array_intersect($chain, $list);
      if (count($point) != 1) {
	throw new UnexpectedValueException(VoteRoleMessage::UNCHAINED_ROUTE);
      }

      $new_vector = ArrayFilter::PickKey($point);
      if ($new_vector != $vector) {
	if ($count++ > 0) {
	  throw new UnexpectedValueException(VoteRoleMessage::INVALID_ROUTE);
	}
	$vector = $new_vector;
      }

      $id = array_shift($point);
      $root_list[] = $id;
      ArrayFilter::Delete($list, $id);
    }

    //-- 投票情報登録 --//
    $target_stack = [];
    $handle_stack = [];
    foreach ($root_list as $id) {
      $target_stack[] = $id;
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
}
