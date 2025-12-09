<?php
/*
  ◆鬼車鳥 (plumage_patron)
  ○仕様
  ・追加役職：吸毒者
*/
RoleLoader::LoadFile('patron');
class Role_plumage_patron extends Role_patron {
  protected function AddDuelistRole(User $user) {
    $user->AddRole('aspirator');
  }
}
