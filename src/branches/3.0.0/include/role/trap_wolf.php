<?php
/*
  ◆狡狼 (trap_wolf)
  ○仕様
  ・罠：罠死 (自動自己設置型)
*/
RoleManager::LoadFile('wolf');
class Role_trap_wolf extends Role_wolf {
  public $ability = 'ability_trap_wolf';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  protected function OutputAddResult() {
    RoleHTML::OutputAbilityResult($this->ability, null);
  }

  final public function SetTrap() {
    $this->AddStack($this->GetID(), 'trap');
  }
}
