<?php
/*
  ◆境界師 (border_priest)
  ○仕様
  ・司祭：自分への投票人数 (2日目以降)
*/
RoleManager::LoadFile('priest');
class Role_border_priest extends Role_priest {
  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  protected function IgnoreSetPriest() {
    return DB::$ROOM->date < 2;
  }

  public function IsAggregatePriest() {
    return false;
  }

  public function Priest() {
    $result = $this->GetResult();
    foreach (DB::$USER->GetRoleID($this->role) as $id) {
      $count = 0;
      foreach (DB::$ROOM->Stack()->Get('vote') as $vote_stack) {
	foreach ($vote_stack as $stack) {
	  if ($stack['target_no'] == $id) $count++;
	}
      }
      DB::$ROOM->ResultAbility($result, $count, null, $id);
    }
  }
}
