<?php
/*
  ◆キューピッド (cupid)
  ○仕様
  ・仲間表示：自分が矢を打った恋人 (自分自身含む)
  ・追加役職：なし
*/
class Role_cupid extends Role {
  public $action      = VoteAction::CUPID;
  public $action_date = RoleActionDate::FIRST;

  protected function GetPartner() {
    $id    = $this->GetID();
    $stack = array();
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsPartner('lovers', $id) || $this->IsCupidPartner($user, $id)) {
	$stack[] = $user->handle_name;
      }
    }
    return array('cupid_pair' => $stack);
  }

  //自分の作った恋人判定
  protected function IsCupidPartner(User $user, $id) {
    return false;
  }

  public function OutputAction() {
    RoleHTML::OutputVote(VoteCSS::CUPID, RoleAbilityMessage::CUPID, $this->action);
  }

  protected function SetVoteNightFilter() {
    $this->SetStack(DB::$USER->Count() < GameConfig::CUPID_SELF_SHOOT, 'self_shoot');
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
    $self_shoot = false; //自分撃ち実行フラグ
    $user_list  = array();
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
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
    $this->SetStack($self_shoot, 'is_self_shoot');
    return null;
  }

  public function VoteNightAction() {
    $role  = $this->GetActor()->GetID('lovers');
    $list  = $this->GetStack('target_list');
    $stack = array();
    foreach ($list as $user) {
      $stack[] = $user->handle_name;
      if ($this->IsLoversTarget($user)) {
	$user->AddRole($role); //恋人セット
      }
      $this->AddCupidRole($user); //役職追加
      $user->Reparse(); //再パース (魂移使判定用：反映が保障されているのは恋人のみ)
    }
    $this->SetStack(ArrayFilter::ConcatKey($list), RequestDataVote::TARGET);
    $this->SetStack(ArrayFilter::Concat($stack), 'target_handle');
    $this->VoteNightCupidAction();
  }

  //恋人対象判定
  protected function IsLoversTarget(User $user) {
    return true;
  }

  //役職追加処理
  protected function AddCupidRole(User $user) {}

  //投票追加処理 (キューピッド専用)
  protected function VoteNightCupidAction() {}

  //全恋人ID取得
  final protected function GetLoversList() {
    $stack = array();
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsRole('lovers')) {
	$stack[] = $user->id;
      }
    }
    return $stack;
  }
}
