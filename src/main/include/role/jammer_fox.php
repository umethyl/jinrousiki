<?php
/*
  ◆月狐 (jammer_fox)
  ○仕様
  ・占い妨害：70%
*/
RoleLoader::LoadFile('child_fox');
class Role_jammer_fox extends Role_child_fox {
  public $mix_in = array('vote' => 'jammer_mad');
  public $result = null;

  protected function IsSetJammer() {
    return Lottery::Percent(70);
  }
}
