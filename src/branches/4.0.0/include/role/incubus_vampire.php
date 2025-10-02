<?php
/*
  ◆青髭公 (incubus_vampire)
  ○仕様
  ・吸血：女性以外なら吸血死
*/
RoleLoader::LoadFile('vampire');
class Role_incubus_vampire extends Role_vampire {
  protected function IsInfect(User $user) {
    return Sex::IsFemale($user);
  }

  protected function InfectFailedAction(User $user) {
    if (! RoleUser::IsAvoid($user)) DB::$USER->Kill($user->id, DeadReason::VAMPIRE_KILLED);
  }
}
