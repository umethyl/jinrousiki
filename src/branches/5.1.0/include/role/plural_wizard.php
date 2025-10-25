<?php
/*
  ◆魔女見習い (plural_wizard)
  ○仕様
  ・魔法：占い師 (複合投票/35%)
  ・天候：霧雨(成功率 100%), 木枯らし(成功率 0%)
  ・占い：通常
  ・占い失敗固定：有効
  ・夜投票所要人数：3人
*/
RoleLoader::LoadFile('wizard');
class Role_plural_wizard extends Role_wizard {
  public $mix_in = ['mage'];
  public $action = VoteAction::PLURAL_WIZARD;
  public $submit = VoteAction::WIZARD;

  protected function GetWizardResultList() {
    return [RoleAbility::MAGE];
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function ValidateVoteNightTargetList(array $list) {
    $this->ValidateVoteNightTargetListRange($list);
  }

  protected function GetVoteNightTargetListRangeMax() {
    return 3;
  }

  public function SetVoteNightTargetList(array $list) {
    $this->SetVoteNightTargetListRange($list);
  }

  protected function GetWizardList() {
    return [1 => VoteAction::MAGE];
  }

  //複数占い
  public function PluralMage(array $list) {
    $this->InitStack();
    foreach ($list as $target_id) {
      $this->Mage(DB::$USER->ByID($target_id));
    }

    //全ての判定を実行後に生存していた場合のみ、結果を登録する
    if ($this->GetActor()->IsLive(true)) {
      foreach ($this->GetStack() as $stack) {
	$this->SaveMageResult($stack['user'], $stack['result'], RoleAbility::MAGE);
      }
    }
    //$this->InitStack(); //再利用は想定していないのでここで消すのもあり
  }

  //占い (妨害 > 呪返し > 占い判定)
  public function Mage(User $user) {
    $stack = $this->GetStack();
    if ($this->IsJammer($user) || $this->CallParent('FixMageFailed')) {
      $stack[] = ['user' => $user, 'result' => $this->GetMageFailed()];
    } elseif ($this->IsCursed($user)) {
      return false;
    } else {
      $stack[] = ['user' => $user, 'result' => $this->GetMageResult($user)];
    }
    $this->SetStack($stack);
  }

  public function FixMageFailed() {
    return false === Lottery::Percent($this->GetMageRate());
  }

  //占い成功率取得
  public function GetMageRate() {
    if (DB::$ROOM->IsEvent('full_wizard')) {
      return 100;
    } elseif (DB::$ROOM->IsEvent('debilitate_wizard')) {
      return 0;
    } else {
      return $this->CallParent('GetPluralMageRate');
    }
  }

  //複数占い成功率取得
  public function GetPluralMageRate() {
    return 35;
  }

  protected function GetMageResult(User $user) {
    return $this->DistinguishMage($user);
  }
}
