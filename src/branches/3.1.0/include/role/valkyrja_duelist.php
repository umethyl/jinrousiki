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
  public $action      = VoteAction::DUELIST;
  public $action_date = RoleActionDate::FIRST;

  protected function GetPartner() {
    $id    = $this->GetID();
    $role  = $this->GetPartnerRole();
    $stack = array();
    foreach (DB::$USER->GetRoleUser($role) as $user) {
      if ($user->IsPartner($role, $id)) {
	$stack[] = $user->handle_name;
      }
    }
    return array($this->GetPartnerHeader() => $stack);
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
    RoleHTML::OutputVote(VoteCSS::DUELIST, RoleAbilityMessage::DUELIST, $this->action);
  }

  protected function SetVoteNightFilter() {
    $flag = $this->CheckSelfShoot() && DB::$USER->Count() < GameConfig::CUPID_SELF_SHOOT;
    $this->SetStack($flag, 'self_shoot');
  }

  //自分撃ちチェック実施判定
  protected function CheckSelfShoot() {
    return true;
  }

  protected function IgnoreVoteCheckboxSelf() {
    return false;
  }

  protected function IgnoreVoteCheckboxDummyBoy() {
    return true;
  }

  protected function IsVoteCheckboxChecked(User $user) {
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

  protected function GetVoteCheckboxType() {
    return OptionFormType::CHECKBOX;
  }

  protected function GetVoteNightNeedCount() {
    return 2;
  }

  public function SetVoteNightUserList(array $list) {
    $self_shoot = false; //自分撃ちフラグ
    $user_list  = array();
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id); //投票先のユーザ情報を取得
      $str  = $this->IgnoreVoteNight($user, $user->IsLive()); //例外判定
      if (! is_null($str)) return $str;
      $user_list[$id] = $user;
      $self_shoot |= $this->IsActor($user); //自分撃ち判定
    }

    if (! $self_shoot) { //自分撃ちエラー判定
      if ($this->FixSelfShoot()) { //自分撃ち固定
	return VoteRoleMessage::TARGET_INCLUDE_MYSELF;
      } elseif ($this->IsSelfShoot()) { //参加人数制限
	return VoteRoleMessage::TARGET_MYSELF_COUNT;
      }
    }
    $this->SetStack($user_list, 'target_list');
    return null;
  }

  public function VoteNightAction() {
    $role  = $this->GetActor()->GetID($this->GetPartnerRole());
    $list  = $this->GetStack('target_list');
    $stack = array();
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
	if ($user->IsLive()) $count++;
      }
    }
    return $target > 0 ? $count == 1 : $actor->IsLive();
  }
}
