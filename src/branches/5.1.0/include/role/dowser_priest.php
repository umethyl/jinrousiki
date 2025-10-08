<?php
/*
  ◆探知師 (dowser_priest)
  ○仕様
  ・司祭：サブ役職
*/
RoleLoader::LoadFile('priest');
class Role_dowser_priest extends Role_priest {
  protected function GetPriestType() {
    return 'sub_role';
  }
}
