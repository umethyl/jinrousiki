<?php
/*
  ◆天人 (revive_priest)
  ○仕様
  ・司祭：蘇生
*/
RoleManager::LoadFile('priest');
class Role_revive_priest extends Role_priest {
  protected function IgnoreResult() {
    return true;
  }

  protected function IgnoreSetPriest() {
    if (DB::$ROOM->IsOpenCast()) return true;

    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($user->IsActive()) return false;
    }
    return true;
  }

  public function Priest() {
    if (! $this->IsPriestReturn()) return;

    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($user->IsDummyBoy() || ! $user->IsActive()) continue;

      if ($user->IsLovers(true) || (DB::$ROOM->date > 3 && $user->IsLive(true))) {
	$user->LostAbility();
      }
      elseif ($user->IsDead(true)) {
	$user->Revive();
	$user->LostAbility();
      }
    }
  }

  //帰還
  public function PriestReturn() {
    $user = $this->GetActor();
    if ($user->IsDummyBoy()) return;
    if ($user->IsLovers(true)) {
      $user->LostAbility();
    }
    elseif ($user->IsLive(true)) {
      DB::$USER->Kill($user->id, 'PRIEST_RETURNED');
    }
  }

  //蘇生判定
  private function IsPriestReturn() {
    $data = $this->GetStack('priest');
    return DB::$ROOM->IsDate(4) || isset($data->crisis) || $data->count['wolf'] == 1 ||
      count(DB::$USER->rows) >= $data->count['total'] * 2;
  }
}
