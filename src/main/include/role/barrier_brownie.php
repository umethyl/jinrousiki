<?php
/*
  ◆産土神 (barrier_brownie)
  ○仕様
  ・護衛：10% (村人陣営限定)
  ・護衛成功登録：なし
*/
class Role_barrier_brownie extends Role {
  public $mix_in = ['guard'];

  public function GetGuard($id) {
    $result = [];
    //村人陣営限定 / 発動率判定 / 産土神は護衛不可
    $user = DB::$USER->ByID($id);
    if ($user->IsRole($this->role) ||
	false === $user->IsWinCamp(Camp::HUMAN) ||
	false === Lottery::Percent(10)) {
      return $result;
    }

    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      $result[] = $user->id; //一人だけ代表で護衛者として扱う
      break;
    }
    return $result;
  }

  public function IgnoreGuardSuccess() {
    return true;
  }
}
