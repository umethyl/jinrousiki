<?php
/*
  ◆紂王 (fire_depraver)
  ○仕様
  ・処刑投票：狐火 (3 の倍数日)
*/
RoleLoader::LoadFile('depraver');
class Role_fire_depraver extends Role_depraver {
  public $mix_in = ['critical_mad'];

  protected function GetStackVoteKillType() {
    return RoleStackVoteKill::INIT;
  }

  protected function IsVoteKillActionTarget(User $user) {
    return DB::$ROOM->date % 3 == 0;
  }

  protected function GetVoteKillActionRole() {
    return 'spell_wisp';
  }
}
