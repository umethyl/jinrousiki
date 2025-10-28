<?php
/*
  ◆水妖姫 (succubus_escaper)
  ○仕様
  ・逃亡失敗：男性以外
*/
RoleLoader::LoadFile('escaper');
class Role_succubus_escaper extends Role_escaper {
  protected function EscapeFailed(User $user) {
    return false === Sex::IsMale($user);
  }
}
