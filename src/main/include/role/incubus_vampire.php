<?php
/*
  ◆青髭公 (incubus_vampire)
  ○仕様
  ・吸血：女性以外なら吸血死
*/
RoleManager::LoadFile('vampire');
class Role_incubus_vampire extends Role_vampire {
  function Infect(User $user) {
    if ($user->IsFemale()) {
      parent::Infect($user);
    }
    elseif (! $user->IsAvoid()) {
      DB::$USER->Kill($user->user_no, 'VAMPIRE_KILLED');
    }
  }
}
