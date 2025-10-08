<?php
/*
  ◆天候：闇夜 (full_escape)
  ○仕様
  ・夜投票封印：罠
*/
class Event_full_escape extends Event {
  public function SealVoteNight(array &$stack) {
    $stack[] = VoteAction::TRAP;
  }
}
