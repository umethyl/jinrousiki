<?php
/*
  ◆萌狐 (cute_fox)
  ○仕様
*/
RoleLoader::LoadFile('fox');
class Role_cute_fox extends Role_fox {
  public $mix_in = array('suspect');
}
