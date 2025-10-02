<?php
/*
  ◆後援者 (patron)
  ○仕様
  ・勝利条件対象役職：受援者
  ・仲間表示役職：受援者
  ・自分撃ちチェック：なし
*/
RoleLoader::LoadFile('valkyrja_duelist');
class Role_patron extends Role_valkyrja_duelist {
  protected function GetPartnerRole() {
    return 'supported';
  }

  protected function GetPartnerHeader() {
    return 'patron_target';
  }

  protected function CheckSelfShoot() {
    return false;
  }

  protected function IgnoreVoteCheckboxSelf() {
    return true;
  }

  protected function GetVoteNightNeedCount() {
    return 1;
  }

  public function SetVoteNightUserList(array $list) {
    $stack = array();
    sort($list);
    foreach ($list as $id) {
      $user = DB::$USER->ByID($id);
      $str  = $this->IgnoreVoteNight($user, $user->IsLive()); //例外判定
      if (! is_null($str)) return $str;
      $stack[$id] = $user;
    }
    $this->SetStack($stack, 'target_list');
    return null;
  }

  //後援者追加役職処理
  final protected function AddPatronRole(User $user, $role) {
    $user->AddRole($this->GetActor()->GetID($role));
  }

  public function Win($winner) {
    $actor = $this->GetActor();
    $id    = $actor->id;
    $role  = $this->GetPartnerRole();
    $count = 0;
    foreach (DB::$USER->GetRoleUser($role) as $user) {
      if ($user->IsPartner($role, $id)) {
	if ($user->IsLive()) return true;
	$count++;
      }
    }
    return $count == 0 && $actor->IsLive();
  }
}
