<?php
/*
  ◆煙々羅 (fire_mad)
  ○仕様
  ・処刑投票：天火付加 (3 の倍数限定)
*/
RoleManager::LoadFile('critical_mad');
class Role_fire_mad extends Role_critical_mad {
  function SetVoteAction(User $user) {
    if (DB::$ROOM->date % 3 == 0) $user->AddRole('black_wisp');
  }
}
