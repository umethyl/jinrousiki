<?php
/*
  ◆受託者 (mind_presage)
  ○仕様
  ・表示：3 日目以降 (付加後の人狼襲撃後)
*/
class Role_mind_presage extends Role {
  public $result = 'PRESAGE_RESULT';

  protected function IgnoreAbility() {
    return DB::$ROOM->date < 3;
  }

  protected function IgnoreImage() {
    return true;
  }
}
