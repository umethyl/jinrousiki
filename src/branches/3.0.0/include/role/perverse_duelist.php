<?php
/*
  ◆天探女 (perverse_duelist)
  ○仕様
  ・追加役職：天邪鬼
*/
RoleManager::LoadFile('valkyrja_duelist');
class Role_perverse_duelist extends Role_valkyrja_duelist {
  protected function AddDuelistRole(User $user) {
    $user->AddRole('perverseness');
  }
}
