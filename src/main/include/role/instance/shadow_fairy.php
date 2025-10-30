<?php
/*
  ◆影妖精 (shadow_fairy)
  ○仕様
  ・悪戯：アイコンコピー
*/
RoleLoader::LoadFile('fairy');
class Role_shadow_fairy extends Role_fairy {
  public function BadStatus() {
    $base_date = DB::$ROOM->date; //判定用の日付
    if ((DB::$ROOM->IsOn(RoomMode::WATCH) || DB::$ROOM->IsOn(RoomMode::SINGLE)) &&
	! RQ::Fetch()->reverse_log) {
      $base_date--;
    }

    $stack = [];
    foreach (DB::$USER->Get() as $user) {
      foreach ($user->GetPartner('bad_status', true) as $id => $date) {
	if ($date != $base_date) {
	  continue;
	}

	$target = DB::$USER->ByID($id);
	if ($target->IsRole($this->role)) {
	  $stack[$target->id] = ['icon' => $user->icon_filename, 'color' => $user->color];
	}
      }
    }

    foreach ($stack as $id => $list) {
      $user = DB::$USER->ByID($id);
      $user->color         = $list['color'];
      $user->icon_filename = $list['icon'];
    }
  }
}
