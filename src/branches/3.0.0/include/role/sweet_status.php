<?php
/*
  ◆悲恋 (sweet_status)
  ○仕様
*/
class Role_sweet_status extends Role {
  protected function IgnoreImage() {
    return ! DB::$ROOM->IsDate(2);
  }

  protected function OutputPartner() {
    if ($this->IgnoreOutputLovers()) return;

    $target = $this->GetActor()->partner_list;
    $stack  = array();
    foreach (DB::$USER->rows as $user) {
      if ($this->IsActor($user)) continue;
      if ($this->IsLovers($user, $target)) {
	$stack[] = $user->GetName(); //憑依追跡
      }
    }
    RoleHTML::OutputPartner($stack, 'partner_header', 'lovers_footer');
  }

  private function IgnoreOutputLovers() {
    return $this->GetActor()->IsRole('lovers', 'fake_lovers');
  }

  private function IsLovers(User $user, array $target) {
    return $this->GetActor()->IsPartner('dummy_chiroptera', $user->id) ||
      (DB::$ROOM->IsDate(1) && $user->IsPartner($this->role, $target));
  }
}
