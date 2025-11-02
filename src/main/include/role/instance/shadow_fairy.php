<?php
/*
  ◆影妖精 (shadow_fairy)
  ○仕様
  ・悪戯：アイコンコピー (旧仕様) / 「変装」付与 (新仕様)
*/
RoleLoader::LoadFile('fairy');
class Role_shadow_fairy extends Role_fairy {
  public function BadStatus() {
    //旧仕様だが、互換性のために残しておく
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

  protected function DisableVoteNightCheckboxSelf() {
    return false;
  }

  protected function FairyAction(User $user) {
    //夜投票開始時点の生存者からランダム
    $stack = DB::$USER->SearchLive();
    ArrayFilter::Delete($stack, $user->id); //対象者は除外
    $id = Lottery::Get(ArrayFilter::GetKeyList($stack));

    $user->AddRole(sprintf('face_status[%d-%d]', $id, DB::$ROOM->date + 1));
  }
}
