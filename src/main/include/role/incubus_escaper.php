<?php
/*
  ◆一角獣 (incubus_escaper)
  ○仕様
  ・逃亡失敗：女性以外
*/
RoleLoader::LoadFile('escaper');
class Role_incubus_escaper extends Role_escaper {
  protected function EscapeFailed(User $user) {
    return false === Sex::IsFemale($user);
  }
}
