<?php
/*
  ◆五徳猫 (revive_mania)
  ○仕様
  ・人狼襲撃：コピー先蘇生
*/
RoleManager::LoadFile('unknown_mania');
class Role_revive_mania extends Role_unknown_mania {
  function WolfEatCounter(User $user) {
    if (DB::$ROOM->IsOpenCast() || DB::$ROOM->IsEvent('no_revive') ||
	is_null($id = $this->GetActor()->GetMainRoleTarget())) {
      return;
    }
    $target = DB::$USER->ByID($id);
    if ($target->IsLive(true) || $target->IsReviveLimited()) return;

    $real = DB::$USER->ByReal($target->id);
    if ($target->IsSame($real)) {
      $target->Revive();
    }
    else { //憑依対応
      $target->ReturnPossessed('possessed');
      $target->Revive(true);
      DB::$ROOM->ResultDead($real->handle_name, 'REVIVE_SUCCESS');
      $real->ReturnPossessed('possessed_target');
    }
  }
}
