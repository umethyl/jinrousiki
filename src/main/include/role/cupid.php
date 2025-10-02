<?php
/*
  ◆キューピッド (cupid)
  ○仕様
  ・仲間表示：自分が矢を打った恋人 (自分自身含む)
  ・追加役職：なし
*/
class Role_cupid extends Role {
  public $action = 'CUPID_DO';
  public $action_date_type = 'first';
  public $self_shoot  = false;
  public $shoot_count = 2;

  protected function OutputPartner() {
    $id    = $this->GetID();
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsPartner('lovers', $id) || $this->IsCupidTarget($user, $id)) {
	$stack[] = $user->handle_name;
      }
    }
    RoleHTML::OutputPartner($stack, 'cupid_pair');
  }

  //自分の恋人判定
  protected function IsCupidTarget(User $user, $id) {
    return false;
  }

  public function OutputAction() {
    RoleHTML::OutputVote('cupid-do', 'cupid_do', $this->action);
  }

  protected function SetVoteNightFilter() {
    $flag = DB::$USER->GetUserCount() < GameConfig::CUPID_SELF_SHOOT;
    $this->SetStack($flag, 'self_shoot');
  }

  public function IsVoteCheckbox(User $user, $live) {
    return $live && ! $user->IsDummyBoy();
  }

  protected function IsVoteCheckboxChecked(User $user) {
    return $this->IsSelfShoot() && $this->IsActor($user);
  }

  //自分撃ち判定
  final protected function IsSelfShoot() {
    return $this->GetStack('self_shoot') || $this->self_shoot;
  }

  protected function GetVoteCheckboxHeader() {
    return RoleHTML::GetVoteCheckboxHeader('checkbox');
  }

  protected function GetVoteNightNeedCount() {
    return $this->shoot_count;
  }

  public function SetVoteNightUserList(array $list) {
    $self_shoot = false; //自分撃ち実行フラグ
    $user_list  = array();
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      //例外判定
      if ($user->IsDead())     return VoteRoleMessage::TARGET_DEAD;
      if ($user->IsDummyBoy()) return VoteRoleMessage::TARGET_DUMMY_BOY;
      $user_list[$id] = $user;
      $self_shoot |= $this->IsActor($user); //自分撃ち判定
    }

    if (! $self_shoot) { //自分撃ちエラー判定
      if ($this->self_shoot)    return VoteRoleMessage::TARGET_INCLUDE_MYSELF; //自分撃ち固定
      if ($this->IsSelfShoot()) return VoteRoleMessage::TARGET_MYSELF_COUNT;   //参加人数
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
      if ($this->IsLoversTarget($user)) $user->AddRole($role); //恋人セット
      $this->AddCupidRole($user); //役職追加
      $user->Reparse(); //再パース (魂移使判定用：反映が保障されているのは恋人のみ)
    }
    $this->SetStack(implode(' ', array_keys($list)), 'target_no');
    $this->SetStack(implode(' ', $stack), 'target_handle');
  }

  //恋人対象判定
  protected function IsLoversTarget(User $user) {
    return true;
  }

  //役職追加処理
  protected function AddCupidRole(User $user) {}

  //全恋人ID取得
  protected function GetLoversList() {
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsLovers()) $stack[] = $user->id;
    }
    return $stack;
  }
}
