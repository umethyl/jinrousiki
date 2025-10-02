<?php
/*
  ◆寒戸婆 (snow_cupid)
  ○仕様
  ・処刑投票：凍傷 (恋人)
*/
RoleManager::LoadFile('cupid');
class Role_snow_cupid extends Role_cupid {
  public $mix_in = 'critical_mad';
  public $vote_day_type = 'init';

  public function SetVoteAction(User $user) {
    if ($user->IsAvoidLovers(true)) return;
    if ($user->IsLovers()) $user->AddDoom(1, 'frostbite');
  }
}
