<?php
/*
  ◆雪女 (snow_trap_mad)
  ○仕様
  ・罠：凍傷
*/
RoleManager::LoadFile('trap_mad');
class Role_snow_trap_mad extends Role_trap_mad {
  public $trap_action = 'snow_trap';
  public $trap_result = 'frostbite';

  protected function IgnoreTrap() { return false; }

  protected function SetTrapAction() { return; }

  protected function GetOtherTrap() { return 'trap'; }

  function TrapKill(User $user, $id) {
    if ($this->IsTrap($id)) $user->AddDoom(1, $this->trap_result);
    return false;
  }

  function DelayTrap(User $user, $id) {
    if ($this->IsTrap($id)) $this->AddSuccess($user->id, $this->trap_result);
    return false;
  }

  function TrapStack(User $user, $id) { return $this->DelayTrap($user, $id); }

  function DelayTrapKill() { return; }
}
