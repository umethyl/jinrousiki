<?php
/*
  ◆猟師 (hunter_guard)
  ○仕様
  ・護衛処理：死亡 (人狼襲撃限定)
  ・狩り：+ 妖狐カウント
*/
RoleLoader::LoadFile('guard');
class Role_hunter_guard extends Role_guard {
  public function GuardAction(User $user) {
    if ($this->GetVoter()->IsSame($this->GetWolfVoter())) {
      DB::$USER->Kill($this->GetID(), DeadReason::WOLF_KILLED);
    }
  }

  protected function IsAddHunt(User $user) {
    return RoleUser::IsFoxCount($user);
  }
}
