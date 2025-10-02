<?php
/*
  ◆火狼 (fire_wolf)
  ○仕様
  ・護衛カウンター：天火
*/
RoleManager::LoadFile('wolf');
class Role_fire_wolf extends Role_wolf {
  function GuardCounter() {
    foreach (array_keys(RoleManager::GetStack('guard_success')) as $id) {
      DB::$USER->ByID($id)->AddRole('black_wisp');
    }
    RoleManager::UnsetStack('guard_success');
  }
}
