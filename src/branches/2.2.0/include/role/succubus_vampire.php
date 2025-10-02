<?php
/*
  ◆飛縁魔 (succubus_vampire)
  ○仕様
  ・吸血：男性以外なら吸血死
*/
RoleManager::LoadFile('vampire');
class Role_succubus_vampire extends Role_vampire {
  protected function Infect(User $user) {
    if ($user->IsMale()) {
      parent::Infect($user);
    }
    elseif (! $user->IsAvoid()) {
      DB::$USER->Kill($user->id, 'VAMPIRE_KILLED');
    }
  }
}
