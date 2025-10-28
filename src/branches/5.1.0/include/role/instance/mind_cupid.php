<?php
/*
  ◆女神 (mind_cupid)
  ○仕様
  ・追加役職：共鳴者 (両方) / 受信者 (自分 / 他人撃ち)
*/
RoleLoader::LoadFile('cupid');
class Role_mind_cupid extends Role_cupid {
  protected function AddCupidRole(User $user) {
    $actor = $this->GetActor();
    $user->AddRole($actor->GetID('mind_friend'));
    if (! $this->GetStack('is_self_shoot')) {
      $actor->AddRole($user->GetID('mind_receiver'));
    }
  }
}
