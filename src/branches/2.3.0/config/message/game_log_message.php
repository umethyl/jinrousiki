<?php
//-- GameLog 専用メッセージ --//
class GameLogMessage {
  /* エラー処理 */
  const CERTIFY = 'ログ閲覧認証エラー';
  const INPUT   = '入力データエラー';
  const PLAYING = '：まだゲームが終了していません';
  const FUTURE  = '：無効なシーンです';

  /* シーン */
  const HEADER     = 'ログ閲覧';
  const BEFOREGAME = '(開始前)';
  const DAY        = '%d 日目 (昼)';
  const NIGHT      = '%d 日目 (夜)';
  const AFTERGAME  = '%d 日目 (終了後)';
  const HEAVEN     = '(霊界)';
}
