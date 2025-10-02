<?php
/*
  ◆白狐 (white_fox)
  ○仕様
  ・人狼襲撃耐性：無し
*/
RoleLoader::LoadFile('fox');
class Role_white_fox extends Role_fox {
  public function IsResistWolf() {
    return false;
  }
}
