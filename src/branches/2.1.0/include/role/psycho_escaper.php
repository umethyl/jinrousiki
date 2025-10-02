<?php
/*
  ◆迷い人 (psycho_escaper)
  ○仕様
  ・逃亡失敗：嘘つき
*/
RoleManager::LoadFile('escaper');
class Role_psycho_escaper extends Role_escaper {
  protected function EscapeFailed(User $user) { return $user->IsLiar(); }
}
