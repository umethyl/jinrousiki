<?php
/*
  ◆共感者 (mind_sympathy)
  ○仕様
*/
class Role_mind_sympathy extends Role {
  protected function IgnoreAbility() { return DB::$ROOM->date < 2; }

  protected function OutputResult() {
    if (DB::$ROOM->IsDate(2)) $this->OutputAbilityResult('SYMPATHY_RESULT');
  }
}
