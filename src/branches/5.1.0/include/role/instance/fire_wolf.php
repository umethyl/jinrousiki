<?php
/*
  ◆火狼 (fire_wolf)
  ○仕様
  ・護衛カウンター：天火
*/
RoleLoader::LoadFile('wolf');
class Role_fire_wolf extends Role_wolf {
  public function GuardCounter() {
    foreach (RoleManager::Stack()->GetKeyList(RoleVoteSuccess::GUARD) as $id) {
      DB::$USER->ByID($id)->AddRole('black_wisp');
    }
    RoleManager::Stack()->Clear(RoleVoteSuccess::GUARD);
  }
}
