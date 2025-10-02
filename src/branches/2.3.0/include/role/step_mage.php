<?php
/*
  ◆審神者 (step_mage)
  ○仕様
*/
RoleManager::LoadFile('mage');
class Role_step_mage extends Role_mage {
  public $action   = 'STEP_MAGE_DO';
  public $submit   = 'mage_do';

  public function IsVoteCheckbox(User $user, $live) {
    return ! $this->IsActor($user);
  }

  protected function GetVoteCheckboxHeader() {
    return '<input type="checkbox" name="target_no[]"';
  }

  public function CheckVoteNightTarget(array $list) {
    $id     = $this->GetActor()->id;
    $max    = count(DB::$USER->rows);
    $vector = null;
    $count  = 0;
    $stack  = array();
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
      $stack[] = $id;
      unset($list[array_search($id, $list)]);
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

    $this->SetStack(implode(' ', $target_stack), 'target_no');
    $this->SetStack(implode(' ', $handle_stack), 'target_handle');
    return null;
  }

  //隣り合っている ID を取得
  final public function GetChain($id, $max) {
    $stack = array();
    if ($id - 5 >= 1)    $stack['N'] = $id - 5;
    if ($id + 5 <= $max) $stack['S'] = $id + 5;
    if ((($id - 1) % 5) != 0 && $id > 1)    $stack ['W'] = $id - 1;
    if ((($id + 1) % 5) != 1 && $id < $max) $stack ['E'] = $id + 1;
    return $stack;
  }

  //足音処理
  public function Step(array $list) {
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
