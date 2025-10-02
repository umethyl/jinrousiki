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

  protected function OutputAddResult() {
    if (DB::$ROOM->date < 3 || DB::$ROOM->IsOption('seal_message')) return;
    $this->OutputAbilityResult('POISON_CAT_RESULT');
  }

  public function OutputAction() {
    if ($this->GetActor()->IsActive() && ! DB::$ROOM->IsOpenCast()) {
      RoleHTML::OutputVote('revive-do', $this->submit, $this->action, $this->not_action);
    }
  }

  public function IsVote() {
    return $this->filter->IsVote() && $this->GetActor()->IsActive();
  }

  public function SetVoteNight() {
    $this->filter->SetVoteNight();
  }

  //投票無効追加判定
  public function IgnoreVoteAction() {
    return $this->GetActor()->IsActive() ? null : VoteRoleMessage::LOST_ABILITY;
  }

  public function GetVoteIconPath(User $user, $live) {
    return $this->filter->GetVoteIconPath($user, $live);
  }

  public function IsVoteCheckbox(User $user, $live) {
    return $this->filter->IsVoteCheckbox($user, $live);
  }

  public function IgnoreVoteNight(User $user, $live) {
    return $this->filter->IgnoreVoteNight($user, $live);
  }

  public function GetReviveRate() {
    return 100;
  }

  public function ReviveAction() {
    $this->GetActor()->LostAbility();
  }
}
