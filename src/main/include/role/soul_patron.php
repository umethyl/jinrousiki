<?php
/*
  ◆家神 (soul_patron)
  ○仕様
  ・追加役職：受援者の役職表示
*/
RoleManager::LoadFile('patron');
class Role_soul_patron extends Role_patron {
  public $result = 'PATRON_RESULT';

  protected function IgnoreResult() {
    return ! DB::$ROOM->IsDate(2);
  }

  protected function AddDuelistRole(User $user) {
    DB::$ROOM->ResultAbility($this->result, $user->main_role, $user->handle_name, $this->GetID());
  }
}
