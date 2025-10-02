<?php
/*
  ◆響狐 (step_fox)
  ○仕様
*/
RoleLoader::LoadFile('fox');
class Role_step_fox extends Role_fox {
  public $mix_in = array('vote' => 'step_mad');
}
