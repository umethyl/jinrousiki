<?php
/*
  ◆司教 (bishop_priest)
  ○仕様
  ・司祭：村人陣営以外の死者 (奇数日 / 3日目以降)
*/
RoleManager::LoadFile('priest');
class Role_bishop_priest extends Role_priest {
  public $priest_type = 'dead';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 2 || DB::$ROOM->date % 2 == 0;
  }

  protected function IgnoreSetPriest() {
    return DB::$ROOM->date < 1 || DB::$ROOM->date % 2 == 1;
  }
}
