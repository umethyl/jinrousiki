<?php
/*
  ◆夜刀神 (revive_avenger)
  ○仕様
*/
RoleLoader::LoadFile('avenger');
class Role_revive_avenger extends Role_avenger {
  public $mix_in = array('revive_pharmacist');
}
