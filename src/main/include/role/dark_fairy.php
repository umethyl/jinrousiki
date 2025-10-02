<?php
/*
  ◆闇妖精 (dark_fairy)
  ○仕様
  ・悪戯：迷彩 (目隠し)
*/
RoleLoader::LoadFile('light_fairy');
class Role_dark_fairy extends Role_light_fairy {
  protected function GetBadStatus() {
    return 'blinder';
  }
}
