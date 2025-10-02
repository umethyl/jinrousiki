<?php
/*
  ◆神主 (bacchus_medium)
  ○仕様
  ・処刑投票：ショック死 (鬼陣営)
*/
RoleManager::LoadFile('medium');
class Role_bacchus_medium extends Role_medium {
  public $mix_in = array('critical_mad');
  public $vote_day_type = 'init';
  public $sudden_death  = 'DRUNK';

  public function SetVoteAction(User $user) {
    if ($user->IsAvoidLovers(true)) return;
    if ($user->IsOgre()) $this->SuddenDeathKill($user->id);
  }
}
