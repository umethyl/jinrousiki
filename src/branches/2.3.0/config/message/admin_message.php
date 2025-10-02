<?php
//-- JinrouAdmin 専用メッセージ --//
class AdminMessage {
  /* 村削除 */
  const DELETE_ROOM = '部屋削除';
  const DELETE_ROOM_SUCCESS = ' 番地を削除しました。トップページに戻ります。';
  const DELETE_ROOM_FAILED  = ' 番地の削除に失敗しました。';

  /* アイコン削除 */
  const DELETE_ICON = 'アイコン削除';
  const DELETE_ICON_NOT_EXISTS = 'ファイルが存在しません';
  const DELETE_ICON_SUCCESS    = '削除完了：登録ページに飛びます。';

  /* ログ生成 */
  const GENERATE_LOG = 'ログ生成';
  const GENERATE_LOG_FORMAT = '%d 番地から %d 番地までを HTML 化しました';

  /* ログ削除 */
  const DELETE_LOG = 'ログ削除';
  const DELETE_LOG_FORMAT = '%d 番地を削除しました';
}
