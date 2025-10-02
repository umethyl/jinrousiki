<?php
/*
  ◆天人 (revive_priest)
  ○仕様
  ・司祭：蘇生
*/
RoleManager::LoadFile('priest');
class Role_revive_priest extends Role_priest {
  protected function GetOutputRole() { return null; }

  function Priest(StdClass $role_flag) {
    $data = $this->GetStack('priest');
    if (DB::$ROOM->date != 4 && ! isset($data->crisis) && $data->count['wolf'] != 1 &&
	count(DB::$USER->rows) < $data->count['total'] * 2) {
      return false;
    }

    foreach ($role_flag->{$this->role} as $uname) {
      $user = DB::$USER->ByUname($uname);
      if ($user->IsLovers() || (DB::$ROOM->date >= 4 && $user->IsLive(true))) {
	$user->LostAbility();
      }
      elseif ($user->IsDead(true)) {
	$user->Revive();
	$user->LostAbility();
      }
    }
  }

  //帰還
  function PriestReturn() {
    $user = $this->GetActor();
    if ($user->IsDummyBoy()) return;
    if ($user->IsLovers()) {
      $user->LostAbility();
    }
    elseif ($user->IsLive(true)) {
      DB::$USER->Kill($user->user_no, 'PRIEST_RETURNED');
    }
  }
}
