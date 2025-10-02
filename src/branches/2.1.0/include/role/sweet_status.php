<?php
/*
  ◆悲恋 (sweet_status)
  ○仕様
*/
class Role_sweet_status extends Role {
  protected function OutputImage() {
    if (DB::$ROOM->date == 2) parent::OutputImage();
  }

  protected function OutputPartner() {
    $stack = array();
    $actor = $this->GetActor();
    if ($actor->IsRole('lovers')) return; //恋人持ちなら処理委託
    foreach (DB::$USER->rows as $user) {
      if ($this->IsActor($user->uname)) continue;
      //夢求愛者対応
      if ($actor->IsPartner('dummy_chiroptera', $user->user_no) ||
	  (DB::$ROOM->date == 1 && $user->IsPartner($this->role, $actor->partner_list))) {
	$stack[] = DB::$USER->GetHandleName($user->uname, true); //憑依追跡
      }
    }
    RoleHTML::OutputPartner($stack, 'partner_header', 'lovers_footer');
  }
}
