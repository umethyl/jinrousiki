<?php
/*
  ◆口寄せ (mind_evoke)
  ○仕様
*/
class Role_mind_evoke extends Role {
  protected function IgnoreAbility() { return DB::$ROOM->date < 2; }
}
