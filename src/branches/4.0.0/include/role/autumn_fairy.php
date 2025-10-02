<?php
/*
  ◆秋妖精 (autumn_fairy)
  ○仕様
  ・悪戯：発言妨害 (秋ですよー)
*/
RoleLoader::LoadFile('fairy');
class Role_autumn_fairy extends Role_fairy {
  protected function GetBadStatus() {
    return RoleTalkMessage::AUTUMN_FAIRY;
  }
}
