<?php
/*
  ◆天候：熱帯夜 (no_dream)
  ○仕様
  ・夜投票封印：夢
*/
class Event_no_dream extends Event {
  public function SealVoteNight(array &$stack) {
    $stack[] = VoteAction::DREAM;
  }
}
