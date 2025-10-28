<?php
/*
  ◆春妖精 (spring_fairy)
  ○仕様
  ・悪戯：発言妨害 (春ですよー)
*/
RoleLoader::LoadFile('fairy');
class Role_spring_fairy extends Role_fairy {
  protected function GetBadStatus() {
    return RoleTalkMessage::SPRING_FAIRY;
  }
}
