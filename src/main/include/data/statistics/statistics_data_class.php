<?php
//-- 定数リスト (Statistics/Stack) --//
final class StatisticsStack {
  const CATEGORY    = 'category';
  const WINNER      = 'winner';
  const ROLE        = 'role';
  const WIN_CAMP    = 'win_camp';
  const WIN_ROLE    = 'win_role';
  const CAMP_APPEAR = 'camp_appear';
  const ROLE_APPEAR = 'role_appear';
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
    StatisticsCount::CAMP => StatisticsStack::CAMP_APPEAR,
    StatisticsCount::ROLE => StatisticsStack::ROLE_APPEAR
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

  //-- リンク --//
  const LINK_TOP   = './';
  const LINK_RESET = 'statistics.php';
  const LINK_SELF  = 'statistics';
  const LINK_LOG   = 'old_log';
}
