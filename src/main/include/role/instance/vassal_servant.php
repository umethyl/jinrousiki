<?php
/*
  ◆家臣 (vassal_servant)
  ○仕様
  ・主支援役職：従者支援
  ・主裏切り：従者支援変化
  ・従者支援(投票数)：支援：+1 / 裏切り：-1
*/
RoleLoader::LoadFile('servant');
class Role_vassal_servant extends RoleAbility_servant {
  protected function GetServantTargetRole() {
    return $this->GetActor()->GetID('serve_support');
  }

  protected function GetVoteDoCount() {
    //生存時のみ有効
    $user = $this->GetActor();
    if ($user->IsDead()) {
      return 0;
    }

    //支援中判定
    return $user->IsActive() ? +1 : -1;
  }
}
