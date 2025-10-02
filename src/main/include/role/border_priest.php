<?php
/*
  ◆境界師 (border_priest)
  ○仕様
  ・司祭：自分への投票人数 (2日目以降)
*/
RoleManager::LoadFile('priest');
class Role_border_priest extends Role_priest {
  protected function GetOutputRole() {
    return DB::$ROOM->date > 2 ? $this->role : null;
  }

  protected function SetPriest() {
    if (DB::$ROOM->date > 1) parent::SetPriest();
    return false;
  }

  function Priest(StdClass $role_flag) {
    $event = $this->GetEvent();
    foreach ($role_flag->{$this->role} as $uname) {
      $user  = DB::$USER->ByUname($uname);
      $count = 0;
      foreach (DB::$ROOM->vote as $vote_stack) {
	foreach ($vote_stack as $stack) {
	  if ($stack['target_no'] == $user->user_no) $count++;
	}
      }
      DB::$ROOM->ResultAbility($event, $count, null, $user->user_no);
    }
  }
}
