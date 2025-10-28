<?php
/*
  ◆天候：雷雨 (full_revive)
  ○仕様
  ・夜投票封印：蘇生妨害
*/
class Event_full_revive extends Event {
  public function SealVoteNight(array &$stack) {
    $stack[] = VoteAction::GRAVE;
  }
}
