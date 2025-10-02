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

  protected function OutputResult() {
    if (DB::$ROOM->date > 2) $this->OutputAbilityResult($this->result);
  }

  protected function InfectVampire(User $user) {
    $this->AddSuccess($user->id, 'vampire_kill');
  }

  protected function InfectAction(User $user) {
    DB::$ROOM->ResultAbility($this->result, $user->main_role, $user->GetName(), $this->GetID());
  }
}
