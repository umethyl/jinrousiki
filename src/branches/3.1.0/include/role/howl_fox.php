<?php
/*
  ◆化狐 (howl_fox)
  ○仕様
*/
RoleLoader::LoadFile('child_fox');
class Role_howl_fox extends Role_child_fox {
  public $mix_in = array('silver_wolf');
  public $action = null;
  public $result = null;
}
