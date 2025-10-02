<?php
/*
  ◆復讐者 (avenger)
  ○仕様
  ・勝利条件対象役職：仇敵
  ・仲間表示役職：仇敵
  ・自分撃ちチェック：なし
  ・追加役職：なし
*/
RoleLoader::LoadFile('valkyrja_duelist');
class Role_avenger extends Role_valkyrja_duelist {
  protected function GetPartnerRole() {
    return 'enemy';
  }

  protected function GetPartnerHeader() {
    return 'avenger_target';
  }

  protected function CheckSelfShoot() {
    return false;
  }

  protected function DisableVoteNightCheckboxSelf() {
    return true;
  }

  protected function GetVoteNightNeedCount() {
    return floor(DB::$USER->Count() / 4);
  }

  public function SetVoteNightTargetList(array $list) {
    $stack = [];
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      $this->ValidateVoteNightTarget($user, $user->IsLive());
      $stack[$id] = $user;
    }
    $this->SetStack($stack, 'target_list');
  }

  public function Win($winner) {
    $actor = $this->GetActor();
    $id    = $actor->id;
    $role  = $this->GetPartnerRole();
    $count = 0;
    foreach (DB::$USER->GetRoleUser($role) as $user) {
      if ($user->IsPartner($role, $id)) {
	if ($user->IsLive()) {
	  return false;
	}
	$count++;
      }
    }
    return $count > 0 || $actor->IsLive();
  }
}
