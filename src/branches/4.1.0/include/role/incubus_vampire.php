<?php
/*
  ◆青髭公 (incubus_vampire)
  ○仕様
  ・吸血：女性以外なら吸血死
*/
RoleLoader::LoadFile('vampire');
class Role_incubus_vampire extends Role_vampire {
  protected function InfectFailed(User $user) {
    return false === Sex::IsFemale($user);
  }

  protected function InfectFailedAction(User $user) {
    if (false === RoleUser::IsAvoid($user)) {
      DB::$USER->Kill($user->id, DeadReason::VAMPIRE_KILLED);
    }
  }
}
