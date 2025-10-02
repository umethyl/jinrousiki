<?php
/*
  ◆受託者 (mind_presage)
  ○仕様
*/
class Role_mind_presage extends Role {
  protected function IgnoreAbility() { return DB::$ROOM->date < 3; }

  protected function OutputImage() { return; }

  protected function OutputResult() { $this->OutputAbilityResult('PRESAGE_RESULT'); }
}
