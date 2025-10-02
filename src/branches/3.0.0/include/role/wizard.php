<?php
/*
  ◆魔法使い (wizard)
  ○仕様
  ・魔法：占い師・精神鑑定士・ひよこ鑑定士・狩人・暗殺者
*/
class Role_wizard extends Role {
  public $action = 'WIZARD_DO';
  public $action_date_type = 'after';
  public $wizard_list = array(
    'mage' => 'MAGE_DO', 'psycho_mage' => 'MAGE_DO', 'guard' => 'GUARD_DO',
    'assassin' => 'ASSASSIN_DO', 'sex_mage' => 'MAGE_DO');
  public $result_list = array('MAGE_RESULT', 'GUARD_SUCCESS', 'GUARD_HUNTED');

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  protected function OutputAddResult() {
    foreach ($this->result_list as $result) {
      $this->OutputAbilityResult($result);
    }
  }

  public function OutputAction() {
    RoleHTML::OutputVote('wizard-do', 'wizard_do', $this->action);
  }

  //魔法セット (返り値：昼：魔法 / 夜：投票タイプ)
  final public function SetWizard() {
    $list  = $this->GetWizard();
    $stack = is_null($this->action) ? $list : array_keys($list);
    if (DB::$ROOM->IsEvent('full_wizard')) {
      $role = array_shift($stack);
    } elseif (DB::$ROOM->IsEvent('debilitate_wizard')) {
      $role = array_pop($stack);
    } else {
      $role = Lottery::Get($stack);
    }
    $this->GetActor()->virtual_role = is_int($role) ? $this->role : $role; //仮想役職を登録

    return is_null($this->action) ? $role : $list[$role];
  }

  //魔法リスト取得
  protected function GetWizard() {
    return $this->wizard_list;
  }
}
