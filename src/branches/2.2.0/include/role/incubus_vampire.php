<?php
/*
  ◆青髭公 (incubus_vampire)
  ○仕様
  ・吸血：女性以外なら吸血死
*/
RoleManager::LoadFile('vampire');
class Role_incubus_vampire extends Role_vampire {
  protected function Infect(User $user) {
    if ($user->IsFemale()) {
      parent::Infect($user);
    }
    elseif (! $user->IsAvoid()) {
      DB::$USER->Kill($user->id, 'VAMPIRE_KILLED');
    }
  }
}
