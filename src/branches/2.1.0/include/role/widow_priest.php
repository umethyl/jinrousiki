<?php
/*
  ◆未亡人 (widow_priest)
  ○仕様
  ・役職表示：村人
  ・司祭：共感者 (身代わり君)
*/
RoleManager::LoadFile('priest');
class Role_widow_priest extends Role_priest {
  public $display_role = 'human';

  protected function GetOutputRole() { return null; }

  protected function SetPriest() {
    if (DB::$ROOM->date == 1 && DB::$ROOM->IsDummyBoy()) parent::SetPriest();
    return false;
  }

  function Priest(StdClass $role_flag) {
    $dummy_boy = DB::$USER->ByID(1);
    $result = $dummy_boy->main_role;
    $target = $dummy_boy->handle_name;
    foreach ($role_flag->{$this->role} as $uname) {
      $user = DB::$USER->ByUname($uname);
      if ($user->IsDummyBoy()) continue;
      $user->AddRole('mind_sympathy');
      DB::$ROOM->ResultAbility('SYMPATHY_RESULT', $result, $target, $user->user_no);
    }
  }
}
