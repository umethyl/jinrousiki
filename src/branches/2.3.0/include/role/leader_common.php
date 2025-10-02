<?php
/*
  ◆指導者 (leader_common)
  ○仕様
  ・発言公開：2日目以降
*/
RoleManager::LoadFile('common');
class Role_leader_common extends Role_common {
  public function IsMindRead() {
    return DB::$ROOM->date > 1;
  }
}
