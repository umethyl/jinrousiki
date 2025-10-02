<?php
/*
  ◆風祝 (revive_medium)
  ○仕様
  ・蘇生率：25% / 誤爆有り
*/
RoleManager::LoadFile('medium');
class Role_revive_medium extends Role_medium {
  public $mix_in = array('vote' => 'poison_cat');

  protected function OutputAddResult() {
    $this->OutputReviveResult();
  }
}
