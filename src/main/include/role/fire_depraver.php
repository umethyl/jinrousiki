<?php
/*
  ◆紂王 (fire_depraver)
  ○仕様
  ・処刑投票：鬼火 (3 の倍数日)
*/
RoleManager::LoadFile('depraver');
class Role_fire_depraver extends Role_depraver {
  public $mix_in = array('critical_mad');
  public $vote_day_type = 'init';

  public function SetVoteAction(User $user) {
    if (DB::$ROOM->date % 3 == 0 && ! $user->IsAvoid()) $user->AddRole('spell_wisp');
  }
}
