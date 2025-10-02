<?php
/*
  ◆天候：新月 (new_moon)
  ○仕様
  ・夜投票封印：占い・魔法・人狼・吸血・悪戯
*/
class Event_new_moon extends Event {
  public function SealVoteNight(array &$stack) {
    RoleManager::Stack()->Set('skip', true); //影響範囲に注意
    array_push($stack,
      VoteAction::MAGE,      VoteAction::STEP_MAGE, VoteAction::VOODOO_KILLER,
      VoteAction::CHILD_FOX, VoteAction::TENGU,
      VoteAction::WIZARD,    VoteAction::SPREAD_WIZARD,
      VoteAction::VAMPIRE,   VoteAction::STEP_VAMPIRE,
      VoteAction::FAIRY
    );
  }
}
