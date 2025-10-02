<?php
/*
  ◆九尾 (voodoo_fox)
  ○仕様
*/
RoleManager::LoadFile('fox');
class Role_voodoo_fox extends Role_fox {
  public $mix_in = array('voodoo_mad');
  public $action = 'VOODOO_FOX_DO';
  public $submit = 'voodoo_do';

  public function OutputAction() {
    RoleHTML::OutputVote('wolf-eat', $this->submit, $this->action);
  }
}
