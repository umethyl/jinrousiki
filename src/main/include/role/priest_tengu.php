<?php
/*
  ◆鼻高天狗 (priest_tengu)
  ○仕様
*/
RoleManager::LoadFile('tengu');
class Role_priest_tengu extends Role_tengu {
  public $mix_in = array('mage', 'chicken', 'priest');

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2 || DB::$ROOM->date % 2 == 1;
  }

  protected function OutputAddResult() {
    $this->OutputPriestResult();
  }

  public function GetPriestType() {
    return 'tengu';
  }
}
