<?php
/*
  ◆戦乙女 (valkyrja_duelist)
  ○仕様
  ・仲間表示：自分の勝利条件対象者
  ・勝利条件対象役職：宿敵
  ・仲間表示役職：宿敵
  ・追加役職：なし
*/
class Role_valkyrja_duelist extends Role {
  public $action = VoteAction::DUELIST;

  protected function GetActionDate() {
    return RoleActionDate::FIRST;
  }

  protected function GetPartner() {
    $id    = $this->GetID();
    $role  = $this->GetPartnerRole();
    $stack = [];
    foreach (DB::$USER->GetRoleUser($role) as $user) {
      if ($user->IsPartner($role, $id)) {
	$stack[] = $user->handle_name;
      }
    }
    return [$this->GetPartnerHeader() => $stack];
  }

  //勝利条件対象役職取得
  protected function GetPartnerRole() {
    return 'rival';
  }

  //仲間表示役職取得
  protected function GetPartnerHeader() {
    return 'duelist_pair';
  }

  public function OutputAction() {
    RoleHTML::OutputVoteNight(VoteCSS::DUELIST, RoleAbilityMessage::DUELIST, $this->action);
  }

  protected function SetVoteNightFilter() {
    $flag = $this->CheckSelfShoot() && DB::$USER->Count() < GameConfig::CUPID_SELF_SHOOT;
    $this->SetStack($flag, 'self_shoot');
  }

  //自分撃ちチェック実施判定
  protected function CheckSelfShoot() {
    return true;
  }

  protected function DisableVoteNightCheckboxSelf() {
    return false;
  }

  protected function DisableVoteNightCheckboxDummyBoy() {
    return true;
  }

  protected function GetVoteNightCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function CheckedVoteNightCheckbox(User $user) {
    return $this->IsSelfShoot() && $this->IsActor($user);
  }

  //自分撃ち判定
  final protected function IsSelfShoot() {
    return $this->FixSelfShoot() || $this->GetStack('self_shoot');
  }

  //自分撃ち固定フラグ
  protected function FixSelfShoot() {
    return false;
  }

  protected function GetVoteNightNeedCount() {
    return 2;
  }

  public function SetVoteNightTargetList(array $list) {
    $self_shoot = false; //自分撃ちフラグ
    $user_list  = [];
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id); //投票先のユーザ情報
      $this->ValidateVoteNightTarget($user, $user->IsLive());
      $user_list[$id] = $user;
      if ($this->IsActor($user)) { //自分撃ち判定
	$self_shoot = true;
      }
    }

    //自分撃ちエラー判定 (自分撃ち固定 > 参加人数制限)
    if (false === $self_shoot) {
      if ($this->FixSelfShoot()) {
	throw new UnexpectedValueException(VoteRoleMessage::TARGET_INCLUDE_MYSELF);
      } elseif ($this->IsSelfShoot()) {
	throw new UnexpectedValueException(VoteRoleMessage::TARGET_MYSELF_COUNT);
      }
    }
    $this->SetStack($user_list, 'target_list');
  }

  public function SetVoteNightTargetListAction() {
    $role  = $this->GetActor()->GetID($this->GetPartnerRole());
    $list  = $this->GetStack('target_list');
    $stack = [];
    foreach ($list as $user) {
      $stack[] = $user->handle_name;
      $user->AddRole($role); //対象役職セット
      $this->AddDuelistRole($user); //役職追加
      $user->Reparse(); //再パース (占い判定等に影響するサブ対策)
    }
    $this->SetStack(ArrayFilter::ConcatKey($list), RequestDataVote::TARGET);
    $this->SetStack(ArrayFilter::Concat($stack), 'target_handle');
  }

  //役職追加処理
  protected function AddDuelistRole(User $user) {}

  //投票集計時追加処理
  public function DuelistAction($target_id) {}

  public function Win($winner) {
    $actor  = $this->GetActor();
    $id     = $actor->id;
    $role   = $this->GetPartnerRole();
    $target = 0;
    $count  = 0;
    foreach (DB::$USER->GetRoleUser($role) as $user) {
      if ($user->IsPartner($role, $id)) {
	$target++;
	if ($user->IsLive()) {
	  $count++;
	}
      }
    }
    return $target > 0 ? $count == 1 : $actor->IsLive();
  }
}
