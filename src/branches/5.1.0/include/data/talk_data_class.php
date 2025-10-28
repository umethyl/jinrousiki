<?php
//-- 定数リスト (Talk/Location) --//
final class TalkLocation {
  const SYSTEM     = 'system';
  const DUMMY_BOY  = 'dummy_boy';
  const INDIVIDUAL = 'individual';
  const COMMON     = 'common';
  const WOLF       = 'wolf';
  const MAD        = 'mad';
  const FOX        = 'fox';
  const MONOLOGUE  = 'self_talk';
  const SECRET     = 'secret';
}

//-- 定数リスト (Talk/Action) --//
final class TalkAction {
  const MORNING   = 'MORNING';
  const NIGHT     = 'NIGHT';
  const OBJECTION = 'OBJECTION';
}

//-- 定数リスト (Talk/Voice) --//
final class TalkVoice {
  const STRONG     = 'strong';
  const NORMAL     = 'normal';
  const WEAK       = 'weak';
  const SECRET     = 'secret';
  const INDIVIDUAL = 'individual';
  const LAST_WORDS = 'last_words';
}

//-- 定数リスト (Talk/Element) --//
final class TalkElement {
  const ID       = 'talk_id';
  const SYMBOL   = 'symbol';
  const NAME     = 'user_info';
  const VOICE    = 'voice';
  const SENTENCE = 'sentence';

  const CSS_ROW  = 'row_class';
  const CSS_USER = 'user_class';
  const CSS_SAY  = 'say_class';

  public static $list = [self::ID, self::SYMBOL, self::NAME, self::VOICE, self::SENTENCE];
  public static $css  = [self::CSS_ROW, self::CSS_USER, self::CSS_SAY];
}

//-- 定数リスト (Talk/CSS) --//
final class TalkCSS {
  const DATE         = 'date-time';
  const SYSTEM       = 'system-user';
  const DUMMY        = 'dummy-boy';
  const COMMON       = 'talk-common';
  const COMMON_SAY   = 'say-common';
  const NIGHT_SELF   = 'night-self-talk';
  const NIGHT_COMMON = 'night-common';
  const NIGHT_WOLF   = 'night-wolf';
  const NIGHT_FOX    = 'night-fox';
}
