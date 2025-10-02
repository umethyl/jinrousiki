<?php
/*
  ◆鋭狼 (sharp_wolf)
  ○仕様
  ・襲撃：危機回避
*/
RoleManager::LoadFile('wolf');
class Role_sharp_wolf extends Role_wolf {
  public $result = 'SHARP_WOLF_RESULT';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2 || DB::$ROOM->IsOption('seal_message');
  }

  public function WolfEatAction(User $user) {
    if (! $user->IsMainGroup('mad') && ! $user->IsPoison()) return false;
    if (DB::$ROOM->IsOption('seal_message')) return true;
    $id = $this->GetWolfVoter()->id;
    DB::$ROOM->ResultAbility($this->result, 'wolf_avoid', $user->GetName(), $id);
    return true;
  }
}
