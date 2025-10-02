<?php
/*
  ◆結界師 (barrier_wizard)
  ○仕様
  ・天候：霧雨(護衛成功率 +25%), 木枯らし(護衛成功率 -25%)
  ・護衛失敗：特殊 (別判定)
  ・護衛処理：なし
*/
RoleLoader::LoadFile('wizard');
class Role_barrier_wizard extends Role_wizard {
  public $mix_in = array('guard');
  public $action = VoteAction::SPREAD_WIZARD;
  public $submit = VoteAction::WIZARD;

  protected function GetWizardResultList() {
    return array(RoleAbility::GUARD);
  }

  protected function GetVoteCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  public function CheckVoteNightTarget(array $list) {
    if (count($list) < 1 || 4 < count($list)) {
      return VoteRoleMessage::INVALID_TARGET_RANGE;
    }
    return null;
  }

  public function SetVoteNightUserList(array $list) {
    $target_stack = array();
    $handle_stack = array();
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      $str  = $this->IgnoreVoteNight($user, DB::$USER->IsVirtualLive($user->id)); //例外判定
      if (! is_null($str)) return $str;
      $target_stack[$id] = DB::$USER->ByReal($id)->id;
      $handle_stack[$id] = $user->handle_name;
    }

    sort($target_stack);
    ksort($handle_stack);
    $this->SetStack(ArrayFilter::Concat($target_stack), RequestDataVote::TARGET);
    $this->SetStack(ArrayFilter::Concat($handle_stack), 'target_handle');
    return null;
  }

  protected function GetWizardList() {
    return array($this->role => VoteAction::SPREAD_WIZARD);
  }

  //護衛先セット (魔法)
  final public function SetWizardGuard($list) {
    $actor     = $this->GetActor();
    $stack     = $this->GetStack(null, true);
    $trapped   = false;
    $frostbite = false;
    foreach (Text::Parse($list) as $id) {
      $user = DB::$USER->ByID($id);
      $stack[$actor->id][] = $user->id;
      $trapped   |= $this->InStack($user->id, RoleVoteTarget::TRAP);      //罠死判定
      $frostbite |= $this->InStack($user->id, RoleVoteTarget::SNOW_TRAP); //凍傷判定
    }
    $this->SetStack($stack);

    if ($trapped) {
      $this->AddSuccess($actor->id, RoleVoteSuccess::TRAPPED);
    } elseif ($frostbite) {
      $this->AddSuccess($actor->id, RoleVoteSuccess::FROSTBITE);
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
