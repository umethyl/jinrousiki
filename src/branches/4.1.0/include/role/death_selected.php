<?php
/*
  ◆オシラ遊び (death_selected)
  ○仕様
  ・表示：当日限定
*/
class Role_death_selected extends Role {
  protected function IgnoreAbility() {
    return false === $this->IsDoom();
  }

  //オシラ遊び死亡処理
  final public function DeathSelectedKill() {
    foreach (DB::$USER->Get() as $user) {
      if ($user->IsDead(true)) {
	continue;
      }

      if ($user->GetVirtual()->IsDoomRole($this->role)) {
	DB::$USER->Kill($user->id, DeadReason::PRIEST_RETURNED);
      }
    }
  }
}
