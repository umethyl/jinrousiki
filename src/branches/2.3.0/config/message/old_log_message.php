<?php
//-- ログ用メッセージ --//
class OldLogMessage {
  /* タイトル */
  const TITLE = '[過去ログ]';

  /* 一覧項目 */
  const NUMBER = '村No';
  const NAME   = '村名';
  const COUNT  = '人数';
  const DATE   = '日数';
  const WIN    = '勝';

  /* 一覧 */
  const LOGIN    = '[再入村]';
  const ADD_ROLE = '[役職表示]';

  /* ページリンク */
  const LINK_ORDER    = '[表示順]';
  const ORDER_NORMAL  = '古↓新';
  const ORDER_REVERSE = '新↓古';
  const ORDER_CHANGE  = '入れ替える';
  const ORDER_RESET   = '元に戻す';
  const LINK_WIN      = '勝敗表示';

  /* 個別ログ */
  const BEFORE = '前';
  const AFTER  = '後';

  /* エラー */
  const NO_LOG       = 'ログはありません。';
  const NOT_FINISHED = 'まだこの部屋のログは閲覧できません。';
}
