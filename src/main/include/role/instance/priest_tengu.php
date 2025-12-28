<?php
/*
  ◆鼻高天狗 (priest_tengu)
  ○仕様
  ・司祭：村人陣営 + 人狼陣営 (偶数日 / 4日目以降)
*/
RoleLoader::LoadFile('tengu');
class Role_priest_tengu extends Role_tengu {
  public $mix_in = ['mage', 'chicken', 'priest'];

  protected function IgnoreResult() {
    return false === DateBorder::Two() && Number::Odd(DB::$ROOM->date, 3);
  }

  protected function OutputAddResult() {
    $this->OutputPriestResult();
  }

  protected function GetPriestType() {
    return 'tengu';
  }
}
