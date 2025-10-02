<?php
/*
  ◆家神 (soul_patron)
  ○仕様
  ・追加役職：受援者の役職表示
*/
RoleLoader::LoadFile('patron');
class Role_soul_patron extends Role_patron {
  public $result = RoleAbility::PATRON;

  protected function IgnoreResult() {
    return ! DB::$ROOM->IsDate(2);
  }

  protected function AddDuelistRole(User $user) {
    DB::$ROOM->ResultAbility($this->result, $user->main_role, $user->handle_name, $this->GetID());
  }
}
