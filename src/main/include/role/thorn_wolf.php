<?php
/*
  ◆荊狼 (thorn_wolf)
  ○仕様
  ・護衛カウンター：荊十字
*/
RoleLoader::LoadFile('wolf');
class Role_thorn_wolf extends Role_wolf {
  public function GuardCounter() {
    foreach (RoleManager::Stack()->GetKeyList(RoleVoteSuccess::GUARD) as $id) {
      $user = DB::$USER->ByID($id);
      if (! RoleUser::IsAvoid($user)) {
	$user->AddRole('thorn_cross');
      }
    }
    RoleManager::Stack()->Clear(RoleVoteSuccess::GUARD);
  }
}
