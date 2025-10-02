<?php
/*
  ◆雪女 (snow_trap_mad)
  ○仕様
  ・罠：凍傷
*/
RoleLoader::LoadFile('trap_mad');
class Role_snow_trap_mad extends Role_trap_mad {
  protected function IgnoreTrap() {
    return false;
  }

  protected function GetSetTrapType() {
    return RoleVoteTarget::SNOW_TRAP;
  }

  protected function SetTrapAction() {
    return;
  }

  protected function GetOtherSetTrapType() {
    return RoleVoteTarget::TRAP;
  }

  protected function GetTrapType() {
    return RoleVoteSuccess::FROSTBITE;
  }

  protected function TrapKillAction(User $user) {
    $user->AddDoom(1, $this->GetTrapType());
  }

  protected function GetTrapKillResult($flag) {
    return false;
  }

  protected function TrapCompositeAction(User $user, $id) {
    return $this->DelayTrap($user, $id);
  }

  protected function DelayTrapKillAction() {
    return;
  }
}
