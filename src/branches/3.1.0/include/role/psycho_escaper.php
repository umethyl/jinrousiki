<?php
/*
  ◆迷い人 (psycho_escaper)
  ○仕様
  ・逃亡失敗：嘘つき
*/
RoleLoader::LoadFile('escaper');
class Role_psycho_escaper extends Role_escaper {
  public $mix_in = array('psycho_mage');

  protected function EscapeFailed(User $user) {
    return $this->IsLiar($user);
  }
}
