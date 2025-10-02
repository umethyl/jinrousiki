<?php
/*
  ◆夏妖精 (summer_fairy)
  ○仕様
  ・悪戯：発言妨害 (夏ですよー)
*/
RoleLoader::LoadFile('fairy');
class Role_summer_fairy extends Role_fairy {
  protected function GetBadStatus() {
    return RoleTalkMessage::SUMMER_FAIRY;
  }
}
