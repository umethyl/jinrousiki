<?php
/*
  ◆司教 (bishop_priest)
  ○仕様
  ・司祭：村人陣営以外の死者 (奇数日 / 3日目以降)
*/
RoleLoader::LoadFile('priest');
class Role_bishop_priest extends Role_priest {
  protected function IgnoreResult() {
    return Number::Even(DB::$ROOM->date, 2);
  }

  protected function IgnoreSetPriest() {
    return Number::Odd(DB::$ROOM->date, 1);
  }

  protected function GetPriestType() {
    return 'dead';
  }
}
