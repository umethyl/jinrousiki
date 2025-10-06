<?php
//-- 定数リスト (Vote/Action) --//
final class VoteAction {
  const GAME_START		= 'GAMESTART';
  const KICK			= 'KICK_DO';
  const VOTE			= 'VOTE_DO';
  const VOTE_KILL		= 'VOTE_KILL';
  const MAGE			= 'MAGE_DO';
  const STEP_MAGE		= 'STEP_MAGE_DO';
  const VOODOO_KILLER		= 'VOODOO_KILLER_DO';
  const GUARD			= 'GUARD_DO';
  const STEP_GUARD		= 'STEP_GUARD_DO';
  const REPORTER		= 'REPORTER_DO';
  const ANTI_VOODOO		= 'ANTI_VOODOO_DO';
  const REVIVE			= 'POISON_CAT_DO';
  const NOT_REVIVE		= 'POISON_CAT_NOT_DO';
  const ASSASSIN		= 'ASSASSIN_DO';
  const NOT_ASSASSIN		= 'ASSASSIN_NOT_DO';
  const STEP_ASSASSIN		= 'STEP_ASSASSIN_DO';
  const SCAN			= 'MIND_SCANNER_DO';
  const STEP_SCAN		= 'STEP_SCANNER_DO';
  const WIZARD			= 'WIZARD_DO';
  const SPREAD_WIZARD		= 'SPREAD_WIZARD_DO';
  const ESCAPE			= 'ESCAPE_DO';
  const WOLF			= 'WOLF_EAT';
  const STEP_WOLF		= 'STEP_WOLF_EAT';
  const SILENT_WOLF		= 'SILENT_WOLF_EAT';
  const JAMMER			= 'JAMMER_MAD_DO';
  const VOODOO_MAD		= 'VOODOO_MAD_DO';
  const STEP			= 'STEP_DO';
  const NOT_STEP		= 'STEP_NOT_DO';
  const DREAM			= 'DREAM_EAT';
  const POSSESSED		= 'POSSESSED_DO';
  const NOT_POSSESSED		= 'POSSESSED_NOT_DO';
  const GRAVE			= 'GRAVE_DO';
  const NOT_GRAVE		= 'GRAVE_NOT_DO';
  const TRAP			= 'TRAP_MAD_DO';
  const NOT_TRAP		= 'TRAP_MAD_NOT_DO';
  const EXIT_DO			= 'EXIT_DO';
  const NOT_EXIT		= 'EXIT_NOT_DO';
  const CHILD_FOX		= 'CHILD_FOX_DO';
  const VOODOO_FOX		= 'VOODOO_FOX_DO';
  const CUPID			= 'CUPID_DO';
  const VAMPIRE			= 'VAMPIRE_DO';
  const STEP_VAMPIRE		= 'STEP_VAMPIRE_DO';
  const FAIRY			= 'FAIRY_DO';
  const OGRE			= 'OGRE_DO';
  const NOT_OGRE		= 'OGRE_NOT_DO';
  const DUELIST			= 'DUELIST_DO';
  const TENGU			= 'TENGU_DO';
  const MANIA			= 'MANIA_DO';
  const DEATH_NOTE		= 'DEATH_NOTE_DO';
  const NOT_DEATH_NOTE		= 'DEATH_NOTE_NOT_DO';
  const HEAVEN			= 'REVIVE_REFUSE';
  const FORCE_SUDDEN_DEATH	= 'FORCE_SUDDEN_DEATH';
  const RESET_TIME		= 'RESET_TIME';
}

//-- 定数リスト (Vote/Element/Kick) --//
final class VoteKickElement {
  const TARGET		= 'target';
}

//-- 定数リスト (Vote/Element/Day) --//
final class VoteDayElement {
  const TARGET		= 'target';
  const VOTE_NUMBER	= 'vote_number';
  const POLL_NUMBER	= 'poll_number';
  const USER_LIST	= 'user_list';
  const LIVE_LIST	= 'live_uname_list';
  const COUNT_LIST	= 'vote_count_list';
  const TARGET_LIST	= 'vote_target_list';
  const MESSAGE_LIST	= 'vote_message_list';
  const MAX_VOTED	= 'max_voted_list';
  const VOTE_KILL	= 'vote_kill_uname';
  const VOTED_USER	= 'vote_kill_user';
  const VOTE_POSSIBLE	= 'vote_possible_list';
  const POLL_LIST	= 'poll_count_list';
  const SUDDEN_DEATH	= 'sudden_death';
}

//-- 定数リスト (Vote/Element/ForceSuddenDeath) --//
final class VoteForceSuddenDeathElement {
  const TARGET		= 'target';
}

//-- 定数リスト (Vote/CSS) --//
final class VoteCSS {
  const MAGE		= 'mage-do';
  const GUARD		= 'guard-do';
  const REVIVE		= 'revive-do';
  const ASSASSIN	= 'assassin-do';
  const SCAN		= 'mind-scanner-do';
  const WIZARD		= 'wizard-do';
  const ESCAPE		= 'escape-do';
  const WOLF		= 'wolf-eat';
  const STEP		= 'step-do';
  const CUPID		= 'cupid-do';
  const VAMPIRE		= 'vampire-do';
  const FAIRY		= 'fairy-do';
  const OGRE		= 'ogre-do';
  const DUELIST		= 'duelist-do';
  const TENGU		= 'tengu-do';
  const MANIA		= 'mania-do';
  const DEATH_NOTE	= 'death-note-do';
}
