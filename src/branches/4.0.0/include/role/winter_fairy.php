<?php
/*
  ◆冬妖精 (winter_fairy)
  ○仕様
  ・悪戯：発言妨害 (冬ですよー)
*/
RoleLoader::LoadFile('fairy');
class Role_winter_fairy extends Role_fairy {
  protected function GetBadStatus() {
    return RoleTalkMessage::WINTER_FAIRY;
  }
}
