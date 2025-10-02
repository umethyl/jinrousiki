<?php
/*
  ◆賢狼 (wise_wolf)
  ○仕様
*/
RoleLoader::LoadFile('wolf');
class Role_wise_wolf extends Role_wolf {
  public $mix_in = array('common');
}
