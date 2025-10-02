<?php
/*
  ◆戦乙女 (valkyrja_duelist)
  ○仕様
  ・仲間表示：自分の勝利条件対象者
  ・追加役職：なし
*/
class Role_valkyrja_duelist extends Role {
  public $action = 'DUELIST_DO';
  public $partner_role   = 'rival';
  public $partner_header = 'duelist_pair';
  public $check_self_shoot = true;
  public $self_shoot  = false;
  public $shoot_count = 2;

  protected function OutputPartner() {
    $id    = $this->GetID();
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsPartner($this->partner_role, $id)) $stack[] = $user->handle_name;
    }
    RoleHTML::OutputPartner($stack, $this->partner_header);
  }

  public function OutputAction() {
    RoleHTML::OutputVote('duelist-do', 'duelist_do', $this->action);
  }

  public function IsVote() {
    return DB::$ROOM->IsDate(1);
  }

  protected function GetIgnoreMessage() {
    return VoteRoleMessage::POSSIBLE_ONLY_FIRST_DAY;
  }

  protected function SetVoteNightFilter() {
    $flag = $this->check_self_shoot && DB::$USER->GetUserCount() < GameConfig::CUPID_SELF_SHOOT;
    $this->SetStack($flag, 'self_shoot');
  }

  public function IsVoteCheckbox(User $user, $live) {
    return $live && ! $user->IsDummyBoy();
  }

  protected function IsVoteCheckboxChecked(User $user) {
    return $this->IsSelfShoot() && $this->IsActor($user);
  }

  protected function GetVoteCheckboxHeader() {
    return '<input type="checkbox" name="target_no[]"';
  }

  protected function GetVoteNightNeedCount() {
    return $this->shoot_count;
  }

  public function SetVoteNightUserList(array $list) {
    $self_shoot = false; //自分撃ちフラグ
    $user_list  = array();
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id); //投票先のユーザ情報を取得
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
    return null;
  }

  public function VoteNightAction() {
    $role  = $this->GetActor()->GetID($this->partner_role);
    $list  = $this->GetStack('target_list');
    $stack = array();
    foreach ($list as $user) {
      $stack[] = $user->handle_name;
      $user->AddRole($role); //対象役職セット
      $this->AddDuelistRole($user); //役職追加
    }
    $this->SetStack(implode(' ', array_keys($list)), 'target_no');
    $this->SetStack(implode(' ', $stack), 'target_handle');
  }

  //自分撃ち判定
  final protected function IsSelfShoot() {
    return $this->GetStack('self_shoot') || $this->self_shoot;
  }

  //役職追加処理
  protected function AddDuelistRole(User $user) {}

  //勝利判定
  public function Win($winner) {
    $actor  = $this->GetActor();
    $id     = $actor->id;
    $target = 0;
    $count  = 0;
    foreach (DB::$USER->rows as $user) {
      if ($user->IsPartner($this->partner_role, $id)) {
	$target++;
	if ($user->IsLive()) $count++;
      }
    }
    return $target > 0 ? $count == 1 : $actor->IsLive();
  }
}
