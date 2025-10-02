<?php
/*
  ◆月妖精 (moon_fairy)
  ○仕様
  ・悪戯：迷彩 (耳栓)
*/
RoleLoader::LoadFile('light_fairy');
class Role_moon_fairy extends Role_light_fairy {
  protected function GetBadStatus() {
    return 'earplug';
  }
}
