<?php
/*
  ◆蛇姫 (miasma_jealousy)
  ○仕様
  ・処刑投票：熱病付加 (恋人限定・確率)
*/
RoleManager::LoadFile('jealousy');
class Role_miasma_jealousy extends Role_jealousy {
  public $mix_in = 'critical_mad';

  function SetVoteAction(User $user) {
    if ($user->IsLovers() && ! $user->IsAvoid() && Lottery::Bool()) $user->AddDoom(1, 'febris');
  }
}
