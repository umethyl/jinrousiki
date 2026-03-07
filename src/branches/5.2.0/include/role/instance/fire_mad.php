<?php
/*
  ◆煙々羅 (fire_mad)
  ○仕様
  ・処刑投票：天火 (3の倍数日)
*/
RoleLoader::LoadFile('critical_mad');
class Role_fire_mad extends Role_critical_mad {
  protected function IgnoreVoteKillAction(User $user) {
    return false;
  }

  protected function IsVoteKillActionTarget(User $user) {
    return DateBorder::OnThree();
  }

  protected function GetVoteKillActionRole() {
    return 'black_wisp';
  }
}
