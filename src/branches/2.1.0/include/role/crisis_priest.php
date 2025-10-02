<?php
/*
  ◆預言者 (crisis_priest)
  ○仕様
  ・役職表示：村人
  ・司祭：人外勝利前日情報 (2日目以降)
*/
RoleManager::LoadFile('priest');
class Role_crisis_priest extends Role_priest {
  public $display_role = 'human';

  protected function GetOutputRole() {
    return DB::$ROOM->date > 1 ? $this->role : null;
  }

  function Priest(StdClass $role_flag) {
    $data = $this->GetStack('priest');
    if (isset($data->crisis)) DB::$ROOM->ResultAbility($this->GetEvent(), $data->crisis);
  }
}
