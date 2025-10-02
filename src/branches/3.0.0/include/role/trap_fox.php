<?php
/*
  ◆狡狐 (trap_fox)
  ○仕様
*/
RoleManager::LoadFile('fox');
class Role_trap_fox extends Role_fox {
  public $mix_in = array('vote' => 'trap_mad');
}
