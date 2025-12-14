<?php
/*
  ◆家臣 (vassal_servant)
  ○仕様
  ・投票数：支援：+1 / 裏切り：-1
*/
RoleLoader::LoadFile('servant');
class Role_vassal_servant extends RoleAbility_servant {
  protected function GetVoteDoCount() {
    //生存時のみ有効
    $user = $this->GetActor();
    if ($user->IsDead()) {
      return 0;
    }

    //裏切り判定
    return $user->IsActive() ? +1 : -1;
  }
}
