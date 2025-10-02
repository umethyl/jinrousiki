<?php
/*
  ◆悲恋 (sweet_status)
  ○仕様
  ・役職表示：2 日目限定
  ・仲間表示：対象者 (恋人表示 / 1 日目限定 / 委託あり)
*/
class Role_sweet_status extends Role {
  public $mix_in = array('lovers');

  protected function IgnoreImage() {
    return ! DB::$ROOM->IsDate(2);
  }

  protected function GetPartner() {
    return $this->GetLoversPartner();
  }

  protected function IgnoreGetLoversPartner() {
    return $this->GetActor()->IsRole('lovers', 'fake_lovers');
  }

  protected function IsLoversPartner(User $user) {
    return $this->GetActor()->IsPartner('dummy_chiroptera', $user->id) ||
      (DB::$ROOM->IsDate(1) && $user->IsPartner($this->role, $this->GetStack()));
  }

  protected function OutputPartnerByType(array $list, $type) {
    RoleHTML::OutputPartner($list, $type, 'lovers_footer');
  }
}
