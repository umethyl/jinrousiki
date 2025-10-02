<?php
/*
  ◆絡新婦 (nephila_cupid)
  ○仕様
  ・投票人数：3人
  ・追加役職：愛人 (両方) + 受信者 (自分)
*/
RoleManager::LoadFile('cupid');
class Role_nephila_cupid extends Role_cupid {
  public $self_shoot  = true;
  public $shoot_count = 3;

  protected function IsCupidTarget(User $user, $id) {
    return $user->IsPartner('fake_lovers', $id);
  }

  protected function IsLoversTarget(User $user) {
    $target_id = $this->GetStack();
    if (is_null($target_id)) { //恋人抽選処理
      $stack = array();
      foreach ($this->GetStack('target_list') as $target) {
	if (! $this->IsActor($target)) $stack[] = $target->id;
      }
      $target_id = Lottery::Get($stack);
      $this->SetStack($target_id);
    }
    return $this->IsActor($user) || $user->id == $target_id;
  }

  protected function AddCupidRole(User $user) {
    if (! $this->IsActor($user)) {
      $user->AddRole($this->GetActor()->GetID('fake_lovers'));
      $this->GetActor()->AddRole($user->GetID('mind_receiver'));
    }
  }
}
