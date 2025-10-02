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

  protected function IgnoreResult() {
    return true;
  }

  protected function IgnoreSetPriest() {
    return ! DB::$ROOM->IsDate(1) || ! DB::$ROOM->IsDummyBoy();
  }

  public function IsAggregatePriest() {
    return false;
  }

  public function Priest() {
    $dummy_boy = DB::$USER->ByID(DB::$USER->GetDummyBoyID());
    $result    = $dummy_boy->main_role;
    $target    = $dummy_boy->handle_name;

    foreach (DB::$USER->GetRoleUser($this->role) as $user) {
      if ($user->IsDummyBoy() || $user->IsDead(true)) continue;
      $user->AddRole('mind_sympathy');
      DB::$ROOM->ResultAbility('SYMPATHY_RESULT', $result, $target, $user->id);
    }
  }
}
