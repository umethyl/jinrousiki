<?php
//-- 定数リスト (Statistics/Stack) --//
final class StatisticsStack {
  const CATEGORY    = 'category';
  const WINNER      = 'winner';
  const ROLE        = 'role';
  const WIN_CAMP    = 'win_camp';
  const DRAW_CAMP   = 'draw_camp';
  const GAME_TYPE   = 'game_type';
  const WIN_ROLE    = 'win_role';
  const LOSE_ROLE   = 'lose_role';
  const DRAW_ROLE   = 'draw_role';
  const LIVE_ROLE   = 'live_role';
  const APPEAR_CAMP = 'appear_camp';
  const APPEAR_ROLE = 'appear_role';
  const COUNT_UP    = 'count_up';
  const CHANGE      = 'change';
}

//-- 定数リスト (Statistics/Operation) --//
final class StatisticsOperation {
  const ROOM = 'room';
  const DATE = 'date';
  const USER = 'user_count';
}

//-- 定数リスト (Statistics/Count) --//
final class StatisticsCount {
  const WIN  = 'win';
  const CAMP = 'camp';
  const ROLE = 'role';
}

//-- 定数リスト (Statistics/Data) --//
final class StatisticsData {
  //-- 種別 --//
  //種別(ゲーム種別)
  public static $category = [
    'normal'		=> '普通',
    'festival'		=> 'お祭り',
    'chaos'		=> '闇鍋',
    'duel'		=> '決闘',
    'gray_random'	=> 'グレラン',
    'step'		=> '足音',
    'quiz'		=> 'クイズ',
  ];

  //種別(稼働数)
  public static $operation = [
    StatisticsOperation::ROOM,
    StatisticsOperation::DATE,
    StatisticsOperation::USER
  ];

  //種別(カウントアップ)
  public static $count = [
    StatisticsCount::WIN  => StatisticsStack::WIN_CAMP,
    StatisticsCount::CAMP => StatisticsStack::APPEAR_CAMP,
    StatisticsCount::ROLE => StatisticsStack::APPEAR_ROLE
  ];

  //-- 種別項目名ー --//
  //種別項目名(稼働数)
  public static $category_header_operation = [
    StatisticsMessage::FIELD_CATEGORY,
    StatisticsMessage::FIELD_ROOM,
    StatisticsMessage::FIELD_DATE,
    StatisticsMessage::FIELD_USER,
    StatisticsMessage::FIELD_SEARCH
  ];

  //種別項目名(勝利陣営数)
  public static $category_header_win_camp = [
    StatisticsMessage::FIELD_CAMP,
    StatisticsMessage::FIELD_APPEAR,
    StatisticsMessage::FIELD_APPEAR_RATE,
    StatisticsMessage::FIELD_WIN_COUNT,
    StatisticsMessage::FIELD_LOSE_COUNT,
    StatisticsMessage::FIELD_DRAW_COUNT,
    StatisticsMessage::FIELD_WIN_RATE,
    StatisticsMessage::FIELD_WIN_APPEAR
  ];

  //種別項目名(出現陣営数)
  public static $category_header_appear_camp = [
    StatisticsMessage::FIELD_CAMP,
    StatisticsMessage::FIELD_APPEAR,
    StatisticsMessage::FIELD_APPEAR_ROOM,
    StatisticsMessage::FIELD_APPEAR_RATE,
    StatisticsMessage::FIELD_WIN_COUNT,
    StatisticsMessage::FIELD_LOSE_COUNT,
    StatisticsMessage::FIELD_DRAW_COUNT,
    StatisticsMessage::FIELD_WIN_RATE,
    StatisticsMessage::FIELD_LIVE,
    StatisticsMessage::FIELD_LIVE_RATE
  ];

  //種別項目名(出現役職数)
  public static $category_header_appear_role = [
    StatisticsMessage::FIELD_ROLE,
    StatisticsMessage::FIELD_APPEAR,
    StatisticsMessage::FIELD_APPEAR_ROOM,
    StatisticsMessage::FIELD_APPEAR_RATE,
    StatisticsMessage::FIELD_WIN_COUNT,
    StatisticsMessage::FIELD_LOSE_COUNT,
    StatisticsMessage::FIELD_DRAW_COUNT,
    StatisticsMessage::FIELD_WIN_RATE,
    StatisticsMessage::FIELD_LIVE,
    StatisticsMessage::FIELD_LIVE_RATE,
    StatisticsMessage::FIELD_SEARCH
  ];

  //-- 役職 --//
  //勝利陣営リスト
  public static $win_camp_list  = [
    WinCamp::HUMAN,
    WinCamp::WOLF,
    WinCamp::FOX,
    WinCamp::LOVERS,
    WinCamp::QUIZ,
    WinCamp::VAMPIRE,
    WinCamp::DRAW,
    WinCamp::NONE
  ];

  //出現陣営リスト
  public static $appear_camp_list  = [
    Camp::HUMAN,
    Camp::WOLF,
    Camp::FOX,
    Camp::LOVERS,
    Camp::QUIZ,
    Camp::VAMPIRE,
    Camp::CHIROPTERA,
    Camp::OGRE,
    Camp::DUELIST,
    Camp::TENGU,
    Camp::MANIA
  ];

  //変化役職変換リスト
  public static $origin_role = [
    'changed_disguise'		=> 'disguise_wolf',
    'changed_therian'		=> 'therian_mad',
    'changed_vindictive'	=> 'vindictive_fox',
    'changed_tailtip'		=> 'tailtip_depraver',
    'copied'			=> 'mania',
    'copied_trick'		=> 'trick_mania',
    'copied_basic'		=> 'basic_mania',
    'copied_nymph'		=> 'nymph_mania',
    'copied_soul'		=> 'soul_mania',
    'copied_teller'		=> 'dummy_mania'
  ];

  //-- リンク --//
  const LINK_TOP   = './';
  const LINK_RESET = 'statistics.php';
  const LINK_SELF  = 'statistics';
  const LINK_LOG   = 'old_log';
}
