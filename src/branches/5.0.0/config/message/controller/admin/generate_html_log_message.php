<?php
//-- JinrouAdminGenerateHTMLLog 専用メッセージ --//
class GenerateHTMLLogMessage {
  /* ログ生成 */
  const TITLE  = 'ログ生成';
  const FORMAT = '%d 番地から %d 番地までを HTML 化しました';

  /* ログ削除 */
  const DELETE_TITLE  = 'ログ削除';
  const DELETE_FORMAT = '%d 番地を削除しました';

  /* エラー処理 */
  const INVALIDE_ROOM_START = '不正な村の開始番号です: %s';
  const INVALIDE_ROOM_END   = '不正な村の終了番号です (開始: %s / 終了: %s)';
  const INVALIDE_MODE       = '不正な動作モードです';
}
