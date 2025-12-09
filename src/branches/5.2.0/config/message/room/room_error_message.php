<?php
//◆文字化け抑制◆//
//-- RoomError 専用メッセージ --//
final class RoomErrorMessage {
  /* 共通 */
  const TITLE    = '村作成 [%s]';
  const FINISHED = 'はすでに終了しています。';

  /* 制限事項 */
  const LIMIT = '制限事項';
  const LIMIT_ACCESS         = '無効なアクセスです。';
  const LIMIT_BLACK_LIST     = '村立て制限ホストです。';
  const LIMIT_PASSWORD       = '村作成パスワードが正しくありません。';
  const LIMIT_MAX_ROOM       = '現在プレイ中の村の数がこのサーバで設定されている最大値を超えています。';
  const LIMIT_ESTABLISH_SELF = 'あなたが立てた村が現在プレイ中です。';
  const LIMIT_ESTABLISH_WAIT = 'サーバで設定されている村立て許可時間間隔を経過していません。';
  const LIMIT_WAIT_FINISH    = '村の決着がつくのを待ってから再度登録してください。';
  const LIMIT_WAIT_TIME      = 'しばらく時間を開けてから再度登録してください。';

  /* 入力エラー */
  const INPUT = '入力エラー';
  const INPUT_HEADER     = 'エラーが発生しました。';
  const INPUT_CHECK_LIST = '以下の項目を再度ご確認ください。';
  const INPUT_EMPTY      = 'が記入されていない。';
  const INPUT_LIMIT      = 'の文字数が長すぎる。';
  const INPUT_LIMIT_OVER = 'が 0 以下、または 99 以上である。';
  const INPUT_NG_WORD    = 'に入力禁止文字列が含まれている。';
  const INPUT_MAX_USER   = '無効な最大人数です。';
  const INPUT_PASSWORD   = '有効なGMログインパスワードが設定されていません。';

  /* リアルタイム制 */
  const INPUT_REAL_TIME_HEADER = 'リアルタイム制の昼・夜の時間';
  const INPUT_REAL_TIME_EM     = 'を全角で入力している。';
  const INPUT_REAL_TIME_NUMBER = 'が数字ではない。';

  /* オプション変更 */
  const CHANGE_PLAYING   = 'はプレイ中です。';
  const CHANGE_NOT_GM    = '%s・%s以外は変更できません。';
  const CHANGE_MAX_USER  = '現在の参加人数より少なくできません。';
  const CHANGE_GM_LOGOUT = '募集停止中はログアウトできません。';
}
