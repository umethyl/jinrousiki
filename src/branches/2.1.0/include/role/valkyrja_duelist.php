<?php
/*
  ◆戦乙女 (valkyrja_duelist)
  ○仕様
  ・仲間表示：自分の勝利条件対象者
  ・追加役職：なし
*/
class Role_valkyrja_duelist extends Role {
  public $action = 'DUELIST_DO';
  public $ignore_message = '初日以外は投票できません';
  public $partner_role   = 'rival';
  public $partner_header = 'duelist_pair';
  public $check_self_shoot = true;
  public $self_shoot = false;
  public $shoot_count = 2;

  protected function OutputPartner() {
    $id = $this->GetID();
    $stack = array();
    foreach (DB::$USER->rows as $user) {
      if ($user->IsPartner($this->partner_role, $id)) $stack[] = $user->handle_name;
    }
    RoleHTML::OutputPartner($stack, $this->partner_header);
  }

  function OutputAction() {
    RoleHTML::OutputVote('duelist-do', 'duelist_do', $this->action);
  }

  function IsVote() { return DB::$ROOM->date == 1; }

  function SetVoteNight() {
    parent::SetVoteNight();
    $flag = $this->check_self_shoot && DB::$USER->GetUserCount() < GameConfig::CUPID_SELF_SHOOT;
    $this->SetStack($flag, 'self_shoot');
  }

  function GetVoteCheckbox(User $user, $id, $live) {
    return $this->IsVoteCheckbox($user, $live) ?
      '<input type="checkbox" name="target_no[]"' .
      ($this->IsSelfShoot() && $this->IsActor($user->uname) ? ' checked' : '') .
      ' id="' . $id . '" value="' . $id . '">'."\n" : '';
  }

  function IsVoteCheckbox(User $user, $live) { return $live && ! $user->IsDummyBoy(); }

  //自分撃ち判定
  function IsSelfShoot() { return $this->GetStack('self_shoot') || $this->self_shoot; }

  function VoteNight() {
    $stack = $this->GetVoteNightTarget();
    //人数チェック
    $count = $this->GetVoteNightTargetCount();
    if (count($stack) != $count) return sprintf('指定人数は %d 人にしてください', $count);

    $self_shoot = false; //自分撃ちフラグ
    $user_list  = array();
    sort($stack);
    foreach ($stack as $id) {
      $user = DB::$USER->ByID($id); //投票先のユーザ情報を取得
      //例外処理
      if ($user->IsDead() || $user->IsDummyBoy()) return '死者と身代わり君には投票できません';
      $user_list[$id] = $user;
      $self_shoot |= $this->IsActor($user->uname); //自分撃ち判定
    }

    if (! $self_shoot) { //自分撃ちエラー判定
      $str = '必ず自分を対象に含めてください';
      if ($this->self_shoot)    return $str; //自分撃ち固定役職
      if ($this->IsSelfShoot()) return '少人数村の場合は、' . $str; //参加人数
    }
    $this->VoteNightAction($user_list, $self_shoot);
    return null;
  }

  //投票人数取得
  function GetVoteNightTargetCount() { return $this->shoot_count; }

  //決闘者陣営の投票処理
  function VoteNightAction(array $list) {
    $role  = $this->GetActor()->GetID($this->partner_role);
    $stack = array();
    foreach ($list as $user) {
      $stack[] = $user->handle_name;
      $user->AddRole($role); //対象役職セット
      $this->AddDuelistRole($user); //役職追加
    }
    $this->SetStack(implode(' ', array_keys($list)), 'target_no');
    $this->SetStack(implode(' ', $stack), 'target_handle');
  }

  //役職追加処理
  protected function AddDuelistRole(User $user) {}

  //勝利判定
  function Win($winner) {
    $actor  = $this->GetActor();
    $id     = $actor->user_no;
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
