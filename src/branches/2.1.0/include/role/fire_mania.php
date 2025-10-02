<?php
/*
  ◆青行灯 (fire_mania)
  ○仕様
  ・追加役職：鬼火
*/
RoleManager::LoadFile('unknown_mania');
class Role_fire_mania extends Role_unknown_mania {
  protected function GetManiaRole(User $user) { return 'wisp'; }
}
