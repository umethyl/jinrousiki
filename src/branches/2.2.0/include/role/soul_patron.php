<?php
/*
  ◆家神 (soul_patron)
  ○仕様
  ・追加役職：受援者の役職結果
*/
RoleManager::LoadFile('patron');
class Role_soul_patron extends Role_patron {
  public $result = 'PATRON_RESULT';

  protected function OutputResult() {
    if (DB::$ROOM->IsDate(2)) $this->OutputAbilityResult($this->result);
  }

  protected function AddDuelistRole(User $user) {
    DB::$ROOM->ResultAbility($this->result, $user->main_role, $user->handle_name, $this->GetID());
  }
}
