<?php
/*
  ◆天候：霜柱 (random_step)
  ○仕様
  ・足音：ランダム発生
*/
class Event_random_step extends Event {
  public function Step() {
    $stack = [];
    foreach (DB::$USER->Get() as $user) {
      if (DB::$USER->IsVirtualLive($user->id)) {
	$stack[] = $user->id;
      }
    }
    //Text::p($stack, '◆random_step');

    $count = 0;
    foreach (Lottery::GetList($stack) as $id) {
      if (false === Lottery::Percent(20)) {
	continue;
      }

      DB::$ROOM->StoreDead($id, DeadReason::STEP);
      if (++$count > 2) {
	break;
      }
    }
  }
}
