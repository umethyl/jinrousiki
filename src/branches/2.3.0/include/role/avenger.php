<?php
/*
  ◆復讐者 (avenger)
  ○仕様
  ・追加役職：なし
*/
RoleManager::LoadFile('valkyrja_duelist');
class Role_avenger extends Role_valkyrja_duelist {
  public $partner_role   = 'enemy';
  public $partner_header = 'avenger_target';
  public $check_self_shoot = false;

  public function IsVoteCheckbox(User $user, $live) {
    return parent::IsVoteCheckbox($user, $live) && ! $this->IsActor($user);
  }

  protected function GetVoteNightNeedCount() {
    return floor(DB::$USER->GetUserCount() / 4);
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

  public function Win($winner) {
    $actor = $this->GetActor();
    $id    = $actor->id;
    $count = 0;
    foreach (DB::$USER->rows as $user) {
      if ($user->IsPartner($this->partner_role, $id)) {
	if ($user->IsLive()) return false;
	$count++;
      }
    }
    return $count > 0 || $actor->IsLive();
  }
}
