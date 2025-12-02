<?php
/*
  ◆超能力者 (esper_wizard)
  ○仕様
  ・能力結果：なし
  ・魔法：悪戯 (特殊)
  ・天候：霧雨(死の宣告), 木枯らし(空中浮遊)
  ・悪戯：サブ役職付加 (死の宣告(4日後)・会心・痛恨・恋耳鳴・爆睡者・狐火・空中浮遊)
*/
RoleLoader::LoadFile('wizard');
class Role_esper_wizard extends Role_wizard {
  public $mix_in = ['fairy'];

  protected function IgnoreWizardResult() {
    return true;
  }

  protected function GetWizardList() {
    return [1 => VoteAction::FAIRY];
  }

  protected function FairyAction(User $user) {
    if ($user->IsDead(true)) {
      return;
    }

    $role = $this->GetWizard($this->GetFairyActionWizardList());
    switch ($role) {
    case 'death_warrant':
      if (RoleUser::Avoid($user)) {
	return;
      }
      $user->AddDoom(4, $role);
      break;

    case 'critical_luck':
    case 'spell_wisp':
      if (RoleUser::Avoid($user)) {
	return;
      }
      $user->AddRole($role);
      break;

    default:
      $user->AddRole($role);
      break;
    }
  }

  //悪戯魔法対象役職取得
  private function GetFairyActionWizardList() {
    return [
      'death_warrant',
      'critical_voter',
      'critical_luck',
      'sweet_ringing',
      'deep_sleep',
      'spell_wisp',
      'levitation'
    ];
  }
}
