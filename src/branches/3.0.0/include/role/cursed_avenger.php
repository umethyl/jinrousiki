<?php
/*
  ◆がしゃどくろ (cursed_avenger)
  ○仕様
  ・処刑投票：死の宣告 (人外カウント)
*/
RoleManager::LoadFile('avenger');
class Role_cursed_avenger extends Role_avenger {
  public $mix_in = array('critical_mad');
  public $vote_day_type = 'init';

  public function SetVoteAction(User $user) {
    if (! $user->IsAvoid() && $user->IsInhuman()) $user->AddDoom(4);
  }
}
