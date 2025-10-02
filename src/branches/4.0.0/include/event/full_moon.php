<?php
/*
  ◆天候：満月 (full_moon)
  ○仕様
  ・夜投票封印：占い妨害・呪術・狩人系
*/
class Event_full_moon extends Event {
  public function SealVoteNight(array &$stack) {
    array_push($stack,
      VoteAction::JAMMER, VoteAction::VOODOO_MAD, VoteAction::VOODOO_FOX,
      VoteAction::GUARD,  VoteAction::STEP_GUARD, VoteAction::REPORTER, VoteAction::ANTI_VOODOO
    );
  }
}
