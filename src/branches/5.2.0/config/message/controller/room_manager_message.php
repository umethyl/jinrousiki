<?php
//-- RoomManager 専用メッセージ --//
final class RoomManagerMessage {
  /* タイトル */
  const TITLE        = '村作成';
  const TITLE_CHANGE = '村オプション変更';

  /* 作成メッセージ */
  const ENTRY  = '%s 村を作成しました。トップページに飛びます。';
  const CHANGE = '村のオプションを変更しました。';

  /* 村情報 */
  const DELETE  = '削除 (緊急用)';
  const WAITING = '募集中';
  const CLOSING = '募集停止中';
  const PLAYING = 'プレイ中';
}
