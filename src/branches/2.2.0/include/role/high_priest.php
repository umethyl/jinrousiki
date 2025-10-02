<?php
/*
  ◆大司祭 (high_priest)
  ○仕様
  ・司祭：司祭＆司教 (5日目以降)
*/
RoleManager::LoadFile('priest');
class Role_high_priest extends Role_priest {
  protected function GetOutputRole() {
    return DB::$ROOM->date > 4 ? (DB::$ROOM->date % 2 == 0 ? 'priest' : 'bishop_priest') : null;
  }

  protected function GetPriestRole(array $list) {
    $role = DB::$ROOM->date % 2 == 1 ? 'priest' : 'bishop_priest';
    return DB::$ROOM->date > 3 && ! in_array($role, $list) ? $role : null;
  }

  function GetPriestType() {
    return DB::$ROOM->date % 2 == 1 ? 'human_side' : 'dead';
  }
}
