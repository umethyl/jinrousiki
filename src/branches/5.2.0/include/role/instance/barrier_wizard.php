<?php
/*
  ◆結界師 (barrier_wizard)
  ○仕様
  ・天候：霧雨(護衛成功率 +25%), 木枯らし(護衛成功率 -25%)
  ・護衛失敗：特殊 (別判定)
  ・護衛処理：なし
  ・夜投票所要人数：4人
*/
RoleLoader::LoadFile('wizard');
class Role_barrier_wizard extends Role_wizard {
  public $mix_in = ['guard'];
  public $action = VoteAction::SPREAD_WIZARD;
  public $submit = VoteAction::WIZARD;

  protected function GetWizardResultList() {
    return [RoleAbility::GUARD];
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function ValidateVoteNightTargetList(array $list) {
    $this->ValidateVoteNightTargetListRange($list);
  }

  protected function GetVoteNightTargetListRangeMax() {
    return 4;
  }

  public function SetVoteNightTargetList(array $list) {
    $this->SetVoteNightTargetListRange($list);
  }

  protected function GetWizardList() {
    return [$this->role => VoteAction::SPREAD_WIZARD];
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
      if ($this->InStack($user->id, RoleVoteTarget::TRAP)) { //罠死判定
	$trapped = true;
      }
      if ($this->InStack($user->id, RoleVoteTarget::SNOW_TRAP)) { //凍傷判定
	$frostbite = true;
      }
    }
    $this->SetStack($stack);

    if (true === $trapped) {
      $this->AddSuccess($actor->id, RoleVoteSuccess::TRAPPED);
    } elseif (true === $frostbite) {
      $this->AddSuccess($actor->id, RoleVoteSuccess::FROSTBITE);
    }
  }

  public function GetGuard($target_id) {
    $result = [];
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
