<?php
/*
  ◆求道者 (basic_mania)
  ○仕様
  ・コピー：基本種
*/
RoleManager::LoadFile('mania');
class Role_basic_mania extends Role_mania {
  public $copied = 'copied_basic';

  protected function GetManiaRole(User $user) { return $user->DistinguishRoleGroup(); }
}
