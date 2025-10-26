<?php
/*
  ◆天候：花曇 (no_contact)
  ○仕様
  ・夜投票封印：接触系 (さとり系に注意)
*/
class Event_no_contact extends Event {
  public function SealVoteNight(array &$stack) {
    RoleManager::Stack()->Set('skip', true); //影響範囲に注意
    array_push($stack,
      VoteAction::REPORTER,
      VoteAction::ASSASSIN, VoteAction::STEP_ASSASSIN,
      VoteAction::SCAN,     VoteAction::STEP_SCAN,     VoteAction::ESCAPE,
      VoteAction::TRAP,     VoteAction::EXIT_DO,
      VoteAction::VAMPIRE,  VoteAction::STEP_VAMPIRE,
      VoteAction::OGRE
    );
  }
}
