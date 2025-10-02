<?php
/*
  ◆共感者 (mind_sympathy)
  ○仕様
  ・表示：2 日目限定
*/
class Role_mind_sympathy extends Role {
  public $result = 'SYMPATHY_RESULT';

  protected function IgnoreAbility() {
    return DB::$ROOM->date < 2;
  }

  protected function IgnoreResult() {
    return ! DB::$ROOM->IsDate(2);
  }
}
