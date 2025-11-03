<?php
/*
  ◆尾先 (tailtip_depraver)
  ○仕様
  ・占いカウンター：妖狐変化
*/
RoleLoader::LoadFile('depraver');
class Role_tailtip_depraver extends Role_depraver {
  //占いカウンター
  public function MageReaction(User $user) {
    $user->ReplaceRole($user->main_role, 'fox');
    $user->AddRole('changed_tailtip');
    $user->changed_fox = true;
  }
}
