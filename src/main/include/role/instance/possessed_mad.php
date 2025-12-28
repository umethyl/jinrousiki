<?php
/*
  ◆犬神 (possessed_mad)
  ○仕様
  ・能力結果：憑依先
  ・憑依無効陣営：妖狐/恋人
  ・投票数：+1 (憑依成立 3 日後)
*/
class Role_possessed_mad extends Role {
  public $mix_in = ['authority'];
  public $action     = VoteAction::POSSESSED;
  public $not_action = VoteAction::NOT_POSSESSED;

  protected function GetActionDate() {
    return RoleActionDate::AFTER;
  }

  protected function IsAddVote() {
    return $this->IsActorActive();
  }

  protected function IgnoreResult() {
    return DateBorder::PreThree() || $this->IsAddVote();
  }

  protected function OutputAddResult() {
    RoleHTML::OutputPossessed();
    if (false === $this->IgnoreFilterVoteDo()) {
      RoleHTML::OutputAbilityResult('ability_possessed_mad', null);
    }
  }

  //現在の憑依先 (Mixin 用)
  final protected function OutputPossessed() {
    if ($this->IgnoreResult()) {
      return;
    }
    RoleHTML::OutputPossessed();
  }

  public function OutputAction() {
    if ($this->IsAddVote()) {
      $str = RoleAbilityMessage::POSSESSED;
      RoleHTML::OutputVoteNight(VoteCSS::WOLF, $str, $this->action, $this->not_action);
    }
  }

  public function IsMindReadPossessed(User $user) {
    return $user->IsSame($this->GetViewer());
  }

  protected function IgnoreFilterVoteDo() {
    $list = $this->GetActor()->GetPartner('possessed_target', true);
    return count($list) < 1 || DateBorder::Future(ArrayFilter::GetMin($list) + 2);
  }

  protected function GetDisabledAddVoteNightMessage() {
    return VoteRoleMessage::LOST_ABILITY;
  }

  protected function FixLiveVoteNightIconPath() {
    return true;
  }

  protected function IsVoteNightCheckboxLive($live) {
    return false === $live;
  }

  protected function DisableVoteNightCheckboxDummyBoy() {
    return true;
  }

  protected function IgnoreCompletedVoteNight() {
    return false === $this->IsAddVote();
  }

  //死者憑依情報セット
  final public function SetPossessedDead(User $user) {
    //無効判定 (厄払い > 蘇生 > 無効陣営 > 憑依制限 > 憑依済み)
    if (RoleUser::GuardCurse($this->GetActor(), false)) {
      return false;
    } elseif ($user->IsOn(UserMode::REVIVE)) {
      return false;
    } elseif ($this->CallParent('IgnorePossessedCamp', $user->GetWinCamp())) {
      return false;
    } elseif (RoleUser::LimitedPossessed($user)) {
      return false;
    } elseif (false === $user->IsSame($user->GetReal())) {
      return false;
    }
    $this->AddStack($user->id, 'possessed_dead');
  }

  //憑依無効陣営判定
  protected function IgnorePossessedCamp($camp) {
    return $camp == Camp::FOX || $camp == Camp::LOVERS;
  }

  //憑依情報登録
  final public function SetPossessed() {
    $stack = $this->GetStack('possessed_dead');
    foreach ($stack as $id => $target_id) {
      if (ArrayFilter::CountKey($stack, $target_id) == 1) { //競合判定
	$this->AddStack($target_id, RoleVoteSuccess::POSSESSED, $id);
      }
    }
  }
}
