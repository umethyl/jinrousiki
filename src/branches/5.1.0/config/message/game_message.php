<?php
//-- Game 用共通メッセージ --//
class GameMessage {
  /* HTML ヘッダタイトル */
  const TITLE = '[プレイ]';

  /* ページ移動 */
  const JUMP_PLAYING   = 'ゲーム画面に移動します。';
  const JUMP_HEAVEN    = '天国モードに切り替えます。';
  const JUMP_AFTERGAME = 'ゲーム終了画面に移動します。';

  /* 村タイトル */
  const ROOM_TITLE_FOOTER   = '村';
  const ROOM_NUMBER_FOOTER  = '番地';
  const ROOM_COMMENT_HEADER = '～';
  const ROOM_COMMENT_FOOTER = '～';
  const ROOM_MAX_USER       = '最大%d人';

  /* 自動更新 */
  const AUTO_RELOAD        = '[自動更新]';
  const AUTO_RELOAD_HEADER = '【';
  const AUTO_RELOAD_FOOTER = '】';
  const AUTO_RELOAD_TIME   = '秒';
  const AUTO_RELOAD_MANUAL = '手動';

  /* ログリンク */
  const LOG_LINK      = '[全体ログ]';
  const LOG_LINK_VIEW = '[ログ]';
  const LOG_LINK_ROLE = '[役職表示ログ]';

  /* プレイログリンク */
  const GAME_LOG_BEFOREGAME = '前';
  const GAME_LOG_DAY        = '昼';
  const GAME_LOG_NIGHT      = '夜';
  const GAME_LOG_AFTERGAME  = '後';
  const GAME_LOG_HEAVEN     = '霊';

  /* タイムテーブル */
  const CLOSING    = '[募集停止中]';
  const TIME_TABLE = '%d 日目<span>(生存者 %d 人)</span>';

  /* タイムリミット */
  const TIME_LIMIT_DAY   = '日没まで ';
  const TIME_LIMIT_NIGHT = '夜明けまで ';

  /* 特殊通知メッセージ */
  const VOTE_ANNOUNCE = '時間がありません。投票してください。'; //会話の制限時間切れ
  const WAIT_MORNING  = '待機時間中です。'; //早朝待機制の待機時間中
  const CLOSE_CAST    = '配役隠蔽中です。'; //配役隠蔽通知 (霊界自動公開モード用)

  /* 生存情報 */
  const LIVE = '生存中';
  const DEAD = '死亡';

  /* 特殊ステータス */
  const TEMPORARY_GM = '仮GM';

  /* 投票 */
  const VOTE_RESET = '＜投票がリセットされました　再度投票してください＞'; //投票リセット

  /* 再投票 */
  const REVOTE = '再投票となりました (%d回 再投票となると引き分けになります)'; //引き分け告知

  /* 天候 */
  const WEATHER = '今日の天候は<span>%s</span>です (%s)';

  /* 遺言 */
  const LAST_WORDS_TITLE  = '夜が明けると前の日に亡くなった方の遺言書が見つかりました';
  const LAST_WORDS_FOOTER = 'さんの遺言';

  /* 投票結果 */
  const VOTE_UNIT   = '票'; //単位
  const VOTE_TARGET = '投票先%s →'; //投票先
  const VOTE_COUNT  = '%d 日目 (%d 回目)'; //投票回数
}
