<?php
/*
  ◆結界師 (barrier_wizard)
  ○仕様
  ・護衛失敗：特殊 (別判定)
  ・護衛処理：なし
*/
RoleManager::LoadFile('wizard');
class Role_barrier_wizard extends Role_wizard {
  public $action   = 'SPREAD_WIZARD_DO';
  public $submit   = 'wizard_do';
  public $wizard_list = array('barrier_wizard' => 'SPREAD_WIZARD_DO');
  public $result_list = array('GUARD_SUCCESS');

  function GetVoteCheckboxHeader() { return '<input type="checkbox" name="target_no[]"'; }

  function VoteNight() {
    $stack = $this->GetVoteNightTarget();
    //人数チェック
    if (count($stack) < 1 || 4 < count($stack)) return '指定人数は1～4人にしてください';

    $target_stack = array();
    $handle_stack = array();
    foreach ($stack as $id) {
      $user = DB::$USER->ByID($id);
      //例外判定
      if ($this->IsActor($user) || ! DB::$USER->IsVirtualLive($id) || $user->IsDummyBoy()) {
	return '自分・死者・身代わり君には投票できません';
      }
      $target_stack[$id] = DB::$USER->ByReal($id)->id;
      $handle_stack[$id] = $user->handle_name;
    }
    sort($target_stack);
    ksort($handle_stack);

    $this->SetStack(implode(' ', $target_stack), 'target_no');
    $this->SetStack(implode(' ', $handle_stack), 'target_handle');
    return null;
  }

  final function SetGuard($list) {
    $actor     = $this->GetActor();
    $stack     = $this->GetStack(null, true);
    $trapped   = false;
    $frostbite = false;
    foreach (explode(' ', $list) as $id) {
      $user = DB::$USER->ByID($id);
      $stack[$actor->id][] = $user->id;
      $trapped   |= in_array($user->id, $this->GetStack('trap'));      //罠死判定
      $frostbite |= in_array($user->id, $this->GetStack('snow_trap')); //凍傷判定
    }
    $this->SetStack($stack);
    if ($trapped) {
      $this->AddSuccess($actor->id, 'trapped');
    }
    elseif ($frostbite) {
      $this->AddSuccess($actor->id, 'frostbite');
    }
  }

  final function GetGuard($target_id) {
    $result = array();
    $rate   = $this->GetGuardRate();
    foreach ($this->GetStack() as $id => $stack) {
      if (in_array($target_id, $stack) && Lottery::Percent((100 - count($stack) * 20) * $rate)) {
	$result[] = $id;
      }
    }
    return $result;
  }

  //護衛成功係数取得
  private function GetGuardRate() {
    return DB::$ROOM->IsEvent('full_wizard') ? 1.25 :
      (DB::$ROOM->IsEvent('debilitate_wizard') ? 0.75 : 1);
  }

  function IgnoreGuard() { return false; }

  function GuardAction() {}
}
