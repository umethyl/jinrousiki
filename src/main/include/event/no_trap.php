<?php
/*
  ◆天候：雪明り (no_trap)
  ○仕様
  ・夜投票封印：罠
*/
class Event_no_trap extends Event {
  public function SealVoteNight(array &$stack) {
    $stack[] = VoteAction::TRAP;
  }
}
