<?php
/*
  ◆がしゃどくろ (cursed_avenger)
  ○仕様
  ・処刑投票：死の宣告付加 (人外カウント限定)
*/
RoleManager::LoadFile('avenger');
class Role_cursed_avenger extends Role_avenger {
  public $mix_in = 'critical_mad';

  function SetVoteAction(User $user) {
    if (! $user->IsAvoid() && $user->IsInhuman()) $user->AddDoom(4);
  }
}
