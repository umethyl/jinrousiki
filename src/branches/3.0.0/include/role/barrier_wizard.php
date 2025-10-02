<?php
/*
  ◆結界師 (barrier_wizard)
  ○仕様
  ・護衛失敗：特殊 (別判定)
  ・護衛処理：なし
*/
RoleManager::LoadFile('wizard');
class Role_barrier_wizard extends Role_wizard {
  public $mix_in = array('guard');
  public $action = 'SPREAD_WIZARD_DO';
  public $submit = 'wizard_do';
  public $wizard_list = array('barrier_wizard' => 'SPREAD_WIZARD_DO');
  public $result_list = array('GUARD_SUCCESS');

  protected function GetVoteCheckboxHeader() {
    return RoleHTML::GetVoteCheckboxHeader('checkbox');
  }

  public function CheckVoteNightTarget(array $list) {
    if (count($list) < 1 || 4 < count($list)) return VoteRoleMessage::INVALID_TARGET_RANGE;
    return null;
  }

  public function SetVoteNightUserList(array $list) {
    $target_stack = array();
    $handle_stack = array();
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      //例外判定
      if (! DB::$USER->IsVirtualLive($id)) return VoteRoleMessage::TARGET_DEAD;
      if ($user->IsDummyBoy())             return VoteRoleMessage::TARGET_DUMMY_BOY;
      if ($this->IsActor($user))           return VoteRoleMessage::TARGET_MYSELF;
      $target_stack[$id] = DB::$USER->ByReal($id)->id;
      $handle_stack[$id] = $user->handle_name;
    }

    sort($target_stack);
    ksort($handle_stack);
    $this->SetStack(implode(' ', $target_stack), 'target_no');
    $this->SetStack(implode(' ', $handle_stack), 'target_handle');
    return null;
  }

  public function SetGuard($list) {
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

  public function GetGuard($target_id) {
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
    if (DB::$ROOM->IsEvent('full_wizard')) {
      return 1.25;
    } elseif (DB::$ROOM->IsEvent('debilitate_wizard')) {
      return 0.75;
    } else {
      return 1;
    }
  }
}
