<?php
/*
  ◆飛縁魔 (succubus_vampire)
  ○仕様
  ・吸血：男性以外なら吸血死
*/
RoleLoader::LoadFile('incubus_vampire');
class Role_succubus_vampire extends Role_incubus_vampire {
  protected function IsInfect(User $user) {
    return Sex::IsMale($user);
  }
}
