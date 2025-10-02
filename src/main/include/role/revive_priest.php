<?php
/*
  ◆天人 (revive_priest)
  ○仕様
  ・能力結果：なし
  ・司祭：蘇生
*/
RoleLoader::LoadFile('priest');
class Role_revive_priest extends Role_priest {
  protected function IgnoreResult() {
    return true;
  }

  protected function IgnoreSetPriest() {
    if (DB::$ROOM->IsOpenCast()) {
      return true;
    }

    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($user->IsActive()) {
	return false;
      }
    }
    return true;
  }

  protected function IgnorePriest() {
    //蘇生判定 (人外勝利前日 / 5日目 / 村の人口が半分 / 生存人狼が1人
    $data = $this->GetStack('priest');
    if (DB::$ROOM->IsDate(4) || isset($data->crisis) || $data->count['wolf'] == 1 ||
	DB::$USER->Count() >= $data->count['total'] * 2) {
      return false;
    } else {
      return true;
    }
  }

  protected function PriestAction() {
    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($user->IsDummyBoy() || false === $user->IsActive()) {
	continue;
      }

      if (RoleUser::IsContainLovers($user) || (DB::$ROOM->date > 3 && $user->IsLive(true))) {
	$user->LostAbility();
      } elseif ($user->IsDead(true)) {
	$user->Revive();
	$user->LostAbility();
      }
    }
  }

  //帰還
  public function PriestReturn() {
    //身代わり君 (無効) > 恋人 (能力喪失) > 生存 (帰還)
    $user = $this->GetActor();
    if ($user->IsDummyBoy()) {
      return;
    } elseif (RoleUser::IsContainLovers($user)) {
      $user->LostAbility();
    } elseif ($user->IsLive(true)) {
      DB::$USER->Kill($user->id, DeadReason::PRIEST_RETURNED);
    }
  }
}
