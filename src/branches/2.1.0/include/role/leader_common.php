<?php
/*
  ◆指導者 (leader_common)
  ○仕様
*/
RoleManager::LoadFile('common');
class Role_leader_common extends Role_common {
  function IsMindRead(){ return DB::$ROOM->date > 1; }
}
