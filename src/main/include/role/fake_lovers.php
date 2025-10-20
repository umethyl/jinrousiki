<?php
/*
  ◆愛人 (fake_lovers)
  ○仕様
  ・仲間表示：対象者 (恋人系委託あり)
*/
RoleLoader::LoadFile('lovers');
class Role_fake_lovers extends Role_lovers {
  protected function IgnoreGetLoversPartner() {
    return $this->GetActor()->IsRole('lovers');
  }

  protected function IsLoversPartner(User $user) {
    return $this->GetActor()->IsPartner($this->role, $user->id) ||
      $this->GetActor()->IsPartner('dummy_chiroptera', $user->id) ||
      (DateBorder::First() && $user->IsPartner('sweet_status', $this->GetStack()));
  }

  protected function OutputAddPartner() {
    $stack = [];
    foreach ($this->GetActor()->GetPartner($this->role, true) as $id) {
      $stack[] = DB::$USER->ByID($id)->handle_name; //憑依追跡不要
    }
    RoleHTML::OutputPartner($stack, 'partner_header', 'fake_lovers_footer');
  }
}
