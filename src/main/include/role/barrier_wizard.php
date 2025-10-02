<?php
/*
  ◆結界師 (barrier_wizard)
  ○仕様
  ・護衛失敗：特殊 (別判定)
  ・護衛処理：なし
*/
RoleManager::LoadFile('wizard');
class Role_barrier_wizard extends Role_wizard {
  public $action = 'SPREAD_WIZARD_DO';
  public $submit = 'wizard_do';
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
      if ($this->IsActor($user->uname) || ! DB::$USER->IsVirtualLive($id) || $user->IsDummyBoy()) {
	return '自分・死者・身代わり君には投票できません';
      }
      $target_stack[$id] = DB::$USER->ByReal($id)->user_no;
      $handle_stack[$id] = $user->handle_name;
    }
    sort($target_stack);
    ksort($handle_stack);

    $this->SetStack(implode(' ', $target_stack), 'target_no');
    $this->SetStack(implode(' ', $handle_stack), 'target_handle');
    return null;
  }

  function SetGuard($list) {
    $actor     = $this->GetActor()->uname;
    $stack     = $this->GetStack(null, true);
    $trapped   = false;
    $frostbite = false;
    foreach (explode(' ', $list) as $id) {
      $uname = DB::$USER->ByID($id)->uname;
      $stack[$actor][] = $uname;
      $trapped   |= in_array($uname, $this->GetStack('trap')); //罠死判定
      $frostbite |= in_array($uname, $this->GetStack('snow_trap')); //凍傷判定
    }
    $this->SetStack($stack);
    if ($trapped) {
      $this->AddSuccess($actor, 'trapped');
    }
    elseif ($frostbite) {
      $this->AddSuccess($actor, 'frostbite');
    }
  }

  function GetGuard($uname, array &$list) {
    $rate = $this->GetGuardRate();
    foreach ($this->GetStack() as $target_uname => $target_list) {
      if (in_array($uname, $target_list) &&
	  mt_rand(1, 100) <= (100 - count($target_list) * 20) * $rate) {
	$list[] = $target_uname;
      }
    }
  }

  //護衛成功係数取得
  private function GetGuardRate() {
    return DB::$ROOM->IsEvent('full_wizard') ? 1.25 :
      (DB::$ROOM->IsEvent('debilitate_wizard') ? 0.75 : 1);
  }

  function GuardFailed() { return false; }

  function GuardAction(User $user, $flag) {}
}
