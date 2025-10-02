<?php
/*
  ◆土蜘蛛 (miasma_mad)
  ○仕様
  ・処刑投票：熱病付加
*/
RoleManager::LoadFile('critical_mad');
class Role_miasma_mad extends Role_critical_mad {
  function SetVoteAction(User $user) {
    if (! $user->IsAvoid()) $user->AddDoom(1, 'febris');
  }
}
