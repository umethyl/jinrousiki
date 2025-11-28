<?php
/*
  ◆天候：サーチライト (no_escape)
  ○仕様
  ・夜投票封印：逃亡・離脱・暗殺
*/
class Event_no_escape extends Event {
  public function SealVoteNight(array &$stack) {
    array_push($stack,
      VoteAction::ESCAPE,
      VoteAction::EXIT_DO,
      VoteAction::ASSASSIN,
      VoteAction::STEP_ASSASSIN
    );
  }
}
