<?php
//-- 定数リスト (Request/Game) --//
final class RequestDataGame {
  const DB     = 'db_no';
  const ID     = 'room_no';
  const RELOAD = 'auto_reload';
  const SOUND  = 'play_sound';
  const ICON   = 'icon';
  const NAME   = 'name';
  const WORDS  = 'last_words_up';
  const DOWN   = 'list_down';
  const ASYNC  = 'async';
}

//-- 定数リスト (Request/Game/Log) --//
final class RequestDataGameLog {
  const DATE  = 'date';
  const SCENE = 'scene';
}

//-- 定数リスト (Request/Room) --//
final class RequestDataRoom {
  const DEAD   = 'dead_mode';
  const HEAVEN = 'heaven_mode';
}

//-- 定数リスト (Request/User) --//
final class RequestDataUser {
  const ID       = 'user_no';
  const UNAME    = 'uname';
  const TRIP     = 'trip';
  const HN       = 'handle_name';
  const PASSWORD = 'password';
  const SEX      = 'sex';
  const PROFILE  = 'profile';
  const ROLE     = 'role';
  const LOGIN    = 'login_manually';
}

//-- 定数リスト (Request/Talk) --//
final class RequestDataTalk {
  const SENTENCE  = 'say';
  const VOICE     = 'font_type';
  const OBJECTION = 'set_objection';
}

//-- 定数リスト (Request/Vote) --//
final class RequestDataVote {
  const ON         = 'vote';
  const TARGET     = 'target_no';
  const SITUATION  = 'situation';
  const ADD_ACTION = 'add_action';
  const BACK_URL   = 'back_url';
}

//-- 定数リスト (Request/Log/Room) --//
final class RequestDataLogRoom {
  const REVERSE     = 'reverse_log';
  const HEAVEN      = 'heaven_talk';
  const HEAVEN_ONLY = 'heaven_only';
  const ROLE        = 'add_role';
  const TIME        = 'time';
  const SEX         = 'sex';
  const WATCH       = 'watch';
  const WOLF        = 'wolf_sight';
  const PERSONAL    = 'personal_result';
}

//-- 定数リスト (Request/Icon) --//
final class RequestDataIcon {
  const ID         = 'icon_no';
  const NUMBER     = 'number_list';
  const NAME       = 'icon_name';
  const CATEGORY   = 'category';
  const APPEARANCE = 'appearance';
  const AUTHOR     = 'author';
  const COLOR      = 'color';
  const KEYWORD    = 'keyword';
  const MULTI      = 'multi_edit';
  const PASSWORD   = 'password';
}
