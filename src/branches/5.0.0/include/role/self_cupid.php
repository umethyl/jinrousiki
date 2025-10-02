<?php
/*
  ◆求愛者 (self_cupid)
  ○仕様
  ・自分撃ち：固定
  ・追加役職：受信者 (自分→相手)
*/
RoleLoader::LoadFile('cupid');
class Role_self_cupid extends Role_cupid {
  protected function FixSelfShoot() {
    return true;
  }

  protected function AddCupidRole(User $user) {
    if (! $this->IsActor($user)) {
      $user->AddRole($this->GetActor()->GetID('mind_receiver'));
    }
  }
}
