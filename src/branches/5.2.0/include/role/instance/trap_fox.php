<?php
/*
  ◆狡狐 (trap_fox)
  ○仕様
*/
RoleLoader::LoadFile('fox');
class Role_trap_fox extends Role_fox {
  public $mix_in = ['vote' => 'trap_mad'];
}
