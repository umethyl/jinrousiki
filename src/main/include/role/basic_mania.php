<?php
/*
  ◆求道者 (basic_mania)
  ○仕様
  ・コピー役職：基本種
*/
RoleLoader::LoadFile('mania');
class Role_basic_mania extends Role_mania {
  protected function GetCopyRole(User $user) {
    return $user->DistinguishRoleGroup();
  }

  protected function GetCopiedRole() {
    return 'copied_basic';
  }
}
