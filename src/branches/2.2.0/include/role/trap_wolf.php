<?php
/*
  ◆狡狼 (trap_wolf)
  ○仕様
  ・罠：罠死 (自動自己設置型)
*/
RoleManager::LoadFile('wolf');
class Role_trap_wolf extends Role_wolf {
  public $ability = 'ability_trap_wolf';

  protected function OutputResult() {
    if (DB::$ROOM->date > 2) RoleHTML::OutputAbilityResult($this->ability, null);
  }

  final function SetTrap() { $this->AddStack($this->GetID(), 'trap'); }
}
