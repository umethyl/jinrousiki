<?php
/*
  ◆影妖精 (shadow_fairy)
  ○仕様
  ・悪戯：アイコンコピー
*/
RoleManager::LoadFile('fairy');
class Role_shadow_fairy extends Role_fairy {
  public function BadStatus(UserData $USERS) {
    $base_date = DB::$ROOM->date; //判定用の日付
    if ((DB::$ROOM->IsOn('watch') || DB::$ROOM->IsOn('single')) && ! RQ::Get()->reverse_log) {
      $base_date--;
    }

    $stack = array();
    foreach ($USERS->rows as $user) {
      foreach ($user->GetPartner('bad_status', true) as $id => $date) {
	if ($date != $base_date) continue;
	$target = $USERS->ByID($id);
	if ($target->IsRole($this->role)) {
	  $stack[$target->id] = array('icon' => $user->icon_filename, 'color' => $user->color);
	}
      }
    }

    foreach ($stack as $id => $list) {
      $user = $USERS->ByID($id);
      $user->color         = $list['color'];
      $user->icon_filename = $list['icon'];
    }
  }
}
