<?php
/*
  ◆仙狐 (revive_fox)
  ○仕様
  ・蘇生率：100% / 誤爆有り
  ・蘇生後：能力喪失
*/
RoleManager::LoadFile('fox');
class Role_revive_fox extends Role_fox {
  public $mix_in = 'poison_cat';
  public $action     = 'POISON_CAT_DO';
  public $not_action = 'POISON_CAT_NOT_DO';
  public $submit     = 'revive_do';
  public $not_submit = 'revive_not_do';
  public $ignore_message = '初日は蘇生できません';

  protected function OutputResult() {
    if (DB::$ROOM->date > 2 && ! DB::$ROOM->IsOption('seal_message')) {
      $this->OutputAbilityResult('POISON_CAT_RESULT');
    }
    parent::OutputResult();
  }

  function OutputAction() {
    if ($this->GetActor()->IsActive() && ! DB::$ROOM->IsOpenCast()) {
      RoleHTML::OutputVote('revive-do', $this->submit, $this->action, $this->not_action);
    }
  }

  function IsVote() { return $this->filter->IsVote() && $this->GetActor()->IsActive(); }

  function SetVoteNight() { $this->filter->SetVoteNight(); }

  function IgnoreVoteAction() {
    return $this->GetActor()->IsActive() ? null : '能力喪失しています';
  }

  function GetVoteIconPath(User $user, $live) {
    return $this->filter->GetVoteIconPath($user, $live);
  }

  function IsVoteCheckbox(User $user, $live) {
    return $this->filter->IsVoteCheckbox($user, $live);
  }

  function IgnoreVoteNight(User $user, $live) {
    return $this->filter->IgnoreVoteNight($user, $live);
  }

  function GetReviveRate() { return 100; }

  function ReviveAction() { $this->GetActor()->LostAbility(); }
}
