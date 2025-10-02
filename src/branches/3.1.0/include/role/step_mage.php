<?php
/*
  ◆審神者 (step_mage)
  ○仕様
*/
RoleLoader::LoadFile('mage');
class Role_step_mage extends Role_mage {
  public $action = VoteAction::STEP_MAGE;
  public $submit = VoteAction::MAGE;

  protected function IsVoteCheckboxLive($live) {
    return true;
  }

  protected function GetVoteCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  public function CheckVoteNightTarget(array $list) {
    return $this->CheckStepVoteNightTarget($list);
  }

  //投票対象チェック (足音用)
  public function CheckStepVoteNightTarget(array $list) {
    $id     = $this->GetID();
    $max    = DB::$USER->Count();
    $vector = null;
    $count  = 0;
    $stack  = array();
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
      $stack[] = $id;
      ArrayFilter::Delete($list, $id);
    } while (count($list) > 0);
    if (count($stack) < 1) return VoteRoleMessage::UNCHAINED_SELF;

    $target = DB::$USER->ByID($id);

    //例外判定
    if (! DB::$USER->IsVirtualLive($id)) return VoteRoleMessage::TARGET_DEAD;
    if ($this->IsActor($target))         return VoteRoleMessage::TARGET_MYSELF;

    $target_stack = array();
    $handle_stack = array();
    foreach ($stack as $id) { //投票順に意味があるので sort しない
      //対象者のみ憑依追跡する
      $target_stack[] = $id == $target->id ? DB::$USER->ByReal($id)->id : $id;
      $handle_stack[] = DB::$USER->ByID($id)->handle_name;
    }

    $this->SetStack(ArrayFilter::Concat($target_stack), RequestDataVote::TARGET);
    $this->SetStack(ArrayFilter::Concat($handle_stack), 'target_handle');
    return null;
  }

  //足音処理
  public function Step(array $list) {
    if ($this->IgnoreStep()) return false;

    array_pop($list); //最後尾は対象者なので除く
    $stack = array();
    foreach ($list as $id) {
      if (DB::$USER->IsVirtualLive($id)) {
	$stack[] = $id;
      }
    }
    if (count($stack) < 1) return true;
    sort($stack);
    return DB::$ROOM->ResultDead(ArrayFilter::Concat($stack), DeadReason::STEP);
  }

  //足音無効判定
  final protected function IgnoreStep() {
    return $this->GetActor()->IsRole('levitation');
  }
}
