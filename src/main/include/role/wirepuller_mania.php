<?php
/*
  ◆黒衣 (wirepuller_mania)
  ○仕様
  ・追加役職：入道
*/
RoleLoader::LoadFile('unknown_mania');
class Role_wirepuller_mania extends Role_unknown_mania {
  protected function GetCopyRole(User $user) {
    return $user->GetID('wirepuller_luck');
  }
}
