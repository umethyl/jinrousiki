<?php
/*
  ◆仲人 (sweet_servant)
  ○仕様
  ・主支援役職：恋耳鳴
  ・主裏切り：臆病者付与
*/
RoleLoader::LoadFile('servant');
class Role_sweet_servant extends RoleAbility_servant {
  protected function GetServantTargetRole() {
    return 'sweet_ringing';
  }

  protected function ServantEndAction(User $user) {
    return $user->AddRole('random_voice');
  }
}
