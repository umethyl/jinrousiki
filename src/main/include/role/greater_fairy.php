<?php
/*
  ◆大妖精 (greater_fairy)
  ○仕様
  ・悪戯：発言妨害 (妖精・春妖精・夏妖精・秋妖精・冬妖精相当のいずれか)
*/
RoleManager::LoadFile('fairy');
class Role_greater_fairy extends Role_fairy {
  protected function GetBadStatus() {
    $stack = array(RoleTalkMessage::COMMON_TALK,
		   RoleTalkMessage::SPRING_FAIRY, RoleTalkMessage::SUMMER_FAIRY,
		   RoleTalkMessage::AUTUMN_FAIRY, RoleTalkMessage::WINTER_FAIRY);
    return Lottery::Get($stack);
  }
}
