<?php
/*
  ◆後援者 (patron)
  ○仕様
*/
RoleManager::LoadFile('valkyrja_duelist');
class Role_patron extends Role_valkyrja_duelist {
  public $partner_role   = 'supported';
  public $partner_header = 'patron_target';
  public $self_shoot = false;
  public $shoot_count = 1;

  public function IsVoteCheckbox(User $user, $live) {
    return parent::IsVoteCheckbox($user, $live) && ! $this->IsActor($user);
  }

  public function SetVoteNightUserList(array $list) {
    $stack = array();
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      //例外判定
      if ($user->IsDead())       return VoteRoleMessage::TARGET_DEAD;
      if ($user->IsDummyBoy())   return VoteRoleMessage::TARGET_DUMMY_BOY;
      if ($this->IsActor($user)) return VoteRoleMessage::TARGET_MYSELF;
      $stack[$id] = $user;
    }
    $this->SetStack($stack, 'target_list');
    return null;
  }

  protected function AddDuelistRole(User $user) {
    if (isset($this->patron_role)) $user->AddRole($this->GetPatronRole());
  }

  //後援者追加役職取得
  protected function GetPatronRole() { return $this->GetActor()->GetID($this->patron_role); }

  public function Win($winner) {
    $actor = $this->GetActor();
    $id    = $actor->id;
    $count = 0;
    foreach (DB::$USER->rows as $user) {
      if ($user->IsPartner($this->partner_role, $id)) {
	if ($user->IsLive()) return true;
	$count++;
      }
    }
    return $count == 0 && $actor->IsLive();
  }
}
