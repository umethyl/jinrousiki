<?php
/*
  ◆犬神 (possessed_mad)
  ○仕様
  ・憑依無効陣営：妖狐/恋人
  ・投票数：+1 (憑依成立 3 日後)
*/
class Role_possessed_mad extends Role {
  public $mix_in = array('authority');
  public $action     = 'POSSESSED_DO';
  public $not_action = 'POSSESSED_NOT_DO';
  public $action_date_type = 'after';
  public $ability = 'ability_possessed_mad';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3 || $this->GetActor()->IsActive();
  }

  protected function OutputAddResult() {
    RoleHTML::OutputPossessed();
    if (! $this->IgnoreFilterVoteDo()) RoleHTML::OutputAbilityResult($this->ability, null);
  }

  //現在の憑依先 (Mixin 用)
  final public function OutputPossessed() {
    if ($this->IgnoreResult()) return;
    RoleHTML::OutputPossessed();
  }

  public function OutputAction() {
    if ($this->GetActor()->IsActive()) {
      RoleHTML::OutputVote('wolf-eat', 'possessed_do', $this->action, $this->not_action);
    }
  }

  public function IsMindReadPossessed(User $user) {
    return $user->IsSame($this->GetViewer());
  }

  protected function IgnoreVoteFilter() {
    return $this->GetActor()->IsActive() ? null : VoteRoleMessage::LOST_ABILITY;
  }

  public function GetVoteIconPath(User $user, $live) {
    return Icon::GetFile($user->icon_filename);
  }

  public function IsVoteCheckbox(User $user, $live) {
    return ! $live && ! $this->IsActor($user) && ! $user->IsDummyBoy();
  }

  protected function IgnoreFinishVote() {
    return ! $this->GetActor()->IsActive();
  }

  public function IgnoreVoteNight(User $user, $live) {
    return $live ? VoteRoleMessage::TARGET_ALIVE : null;
  }

  public function IgnoreFilterVoteDo() {
    $list = $this->GetActor()->GetPartner('possessed_target', true);
    return count($list) < 1 || DB::$ROOM->date < min(array_keys($list)) + 2;
  }

  //憑依情報セット
  final public function SetPossessed(User $user) {
    foreach (RoleManager::LoadFilter('guard_curse') as $filter) { //厄払い判定
      if ($filter->IsGuard($this->GetID())) return false;
    }

    //無効判定 (蘇生/憑依制限/無効陣営/憑依済み)
    $class = $this->GetParent($method = 'IgnorePossessed');
    if ($user->revive_flag || $user->IsPossessedLimited() ||
	$class->$method($user->GetCamp(true)) || ! $user->IsSame(DB::$USER->ByReal($user->id))) {
      return false;
    }
    $this->AddStack($user->id, 'possessed_dead');
  }

  //無効陣営判定
  public function IgnorePossessed($camp) {
    return $camp == 'fox' || $camp == 'lovers';
  }

  //憑依情報登録
  final public function Possessed() {
    $stack = $this->GetStack('possessed_dead');
    foreach ($stack as $id => $target_id) {
      if (count(array_keys($stack, $target_id)) == 1) { //競合判定
	$this->AddStack($target_id, 'possessed', $id);
      }
    }
  }
}
