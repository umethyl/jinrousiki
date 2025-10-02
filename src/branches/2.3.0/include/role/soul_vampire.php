<?php
/*
  ◆吸血姫 (soul_vampire)
  ○仕様
  ・対吸血：反射
  ・吸血：役職取得
*/
RoleManager::LoadFile('vampire');
class Role_soul_vampire extends Role_vampire {
  public $result = 'VAMPIRE_RESULT';

  protected function IgnoreResult() {
    return DB::$ROOM->date < 3;
  }

  protected function InfectVampire(User $user) {
    if (! $user->IsAvoid()) $this->AddSuccess($user->id, 'vampire_kill');
  }

  protected function InfectAction(User $user) {
    DB::$ROOM->ResultAbility($this->result, $user->main_role, $user->GetName(), $this->GetID());
  }
}
