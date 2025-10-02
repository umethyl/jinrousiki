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
    if (! DB::$ROOM->IsDate(4) && ! isset($data->crisis) && $data->count['wolf'] != 1 &&
	count(DB::$USER->rows) < $data->count['total'] * 2) {
      return false;
    }

    foreach ($role_flag->{$this->role} as $id) {
      $user = DB::$USER->ByID($id);
      if ($user->IsLovers() || (DB::$ROOM->date > 3 && $user->IsLive(true))) {
	$user->LostAbility();
      }
      elseif ($user->IsDead(true)) {
	$user->Revive();
	$user->LostAbility();
      }
    }
  }

  //帰還
  final function PriestReturn() {
    $user = $this->GetActor();
    if ($user->IsDummyBoy()) return;
    if ($user->IsLovers()) {
      $user->LostAbility();
    }
    elseif ($user->IsLive(true)) {
      DB::$USER->Kill($user->id, 'PRIEST_RETURNED');
    }
  }
}
