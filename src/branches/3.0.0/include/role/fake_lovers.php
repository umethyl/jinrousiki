<?php
/*
  ◆愛人 (fake_lovers)
  ○仕様
  ・仲間表示：憑依追跡あり
*/
class Role_fake_lovers extends Role {
  protected function IgnoreImage() {
    return true;
  }

  protected function OutputPartner() {
    $this->OutputLovers();
    foreach ($this->GetActor()->GetPartner($this->role, true) as $id) {
      $stack[] = DB::$USER->ByID($id)->handle_name; //憑依追跡不要
    }
    RoleHTML::OutputPartner($stack, 'partner_header', 'fake_lovers_footer');
  }

  private function OutputLovers() {
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
    return $this->GetActor()->IsRole('lovers');
  }

  private function IsLovers(User $user, array $target) {
    return $this->GetActor()->IsPartner($this->role, $user->id) ||
      $this->GetActor()->IsPartner('dummy_chiroptera', $user->id) ||
      (DB::$ROOM->IsDate(1) && $user->IsPartner('sweet_status', $target));
  }
}
