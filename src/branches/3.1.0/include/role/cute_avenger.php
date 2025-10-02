<?php
/*
  ◆草履大将 (cute_avenger)
  ○仕様
*/
RoleLoader::LoadFile('avenger');
class Role_cute_avenger extends Role_avenger {
  public $mix_in = array('suspect');
}
