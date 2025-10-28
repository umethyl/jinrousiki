<?php
/*
  ◆魔法使い (wizard)
  ○仕様
  ・能力結果：魔法
  ・魔法：占い師・精神鑑定士・ひよこ鑑定士・狩人・暗殺者
  ・天候：霧雨(占い師), 木枯らし(ひよこ鑑定士)
*/
class Role_wizard extends Role {
  public $action = VoteAction::WIZARD;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IgnoreResult() {
    return DateBorder::PreThree();
  }

  protected function OutputAddResult() {
    foreach ($this->GetWizardResultList() as $result) {
      RoleHTML::OutputResult($result);
    }
  }

  //能力結果表示対象役職取得
  protected function GetWizardResultList() {
    return [RoleAbility::MAGE, RoleAbility::GUARD, RoleAbility::HUNTED];
  }

  public function OutputAction() {
    RoleHTML::OutputVoteNight(VoteCSS::WIZARD, RoleAbilityMessage::WIZARD, $this->action);
  }

  //魔法セット (返り値：昼：魔法 / 夜：投票タイプ)
  final public function SetWizard() {
    $list = $this->GetWizardList();
    $role = $this->GetWizard((null === $this->action) ? $list : array_keys($list));
    $this->GetActor()->virtual_role = (is_int($role) ? $this->role : $role); //仮想役職を登録
    return (null === $this->action) ? $role : $list[$role];
  }

  //魔法リスト取得
  protected function GetWizardList() {
    return [
      'mage'		=> VoteAction::MAGE,
      'psycho_mage'	=> VoteAction::MAGE,
      'guard'		=> VoteAction::GUARD,
      'assassin'	=> VoteAction::ASSASSIN,
      'sex_mage'	=> VoteAction::MAGE
    ];
  }

  //魔法取得
  final protected function GetWizard(array $list) {
    if (DB::$ROOM->IsEvent('full_wizard')) {
      return array_shift($list);
    } elseif (DB::$ROOM->IsEvent('debilitate_wizard')) {
      return array_pop($list);
    } else {
      return Lottery::Get($list);
    }
  }
}
