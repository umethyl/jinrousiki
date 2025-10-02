<?php
//-- RoomManager 専用メッセージ --//
class RoomManagerMessage {
  /* タイトル */
  const TITLE          = '村作成';
  const TITLE_CHANGE   = '村オプション変更';
  const TITLE_DESCRIBE = '村情報表示';

  /* 村作成画面 */
  const NOT_ESTABLISH = '村作成はできません。';
  const ROOM_PASSWORD = '村作成パスワード';
  const SUBMIT_CREATE = '作成';
  const SUBMIT_CHANGE = '変更';

  /* 作成メッセージ */
  const ENTRY  = '%s 村を作成しました。トップページに飛びます。';
  const CHANGE = '村のオプションを変更しました。';

  /* 村情報 */
  const DELETE  = '削除 (緊急用)';
  const WAITING = '募集中';
  const PLAYING = 'プレイ中';

  /* エラー表示 */
  const ERROR = '村作成 [%s]';
  const ERROR_HEADER      = 'エラーが発生しました。';
  const ERROR_CHECK_LIST  = '以下の項目を再度ご確認ください。';
  const ERROR_WAIT_FINISH = '村の決着がつくのを待ってから再度登録してください。';
  const ERROR_WAIT_TIME   = 'しばらく時間を開けてから再度登録してください。';

  /* エラー共通 */
  const ERROR_FINISHED = 'はすでに終了しています。';

  /* 制限事項 */
  const ERROR_LIMIT = '制限事項';
  const ERROR_LIMIT_ACCESS         = '無効なアクセスです。';
  const ERROR_LIMIT_BLACK_LIST     = '村立て制限ホストです。';
  const ERROR_LIMIT_PASSWORD       = '村作成パスワードが正しくありません。';
  const ERROR_LIMIT_MAX_ROOM       = '現在プレイ中の村の数がこのサーバで設定されている最大値を超えています。';
  const ERROR_LIMIT_ESTABLISH      = 'あなたが立てた村が現在プレイ中です。';
  const ERROR_LIMIT_ESTABLISH_WAIT = 'サーバで設定されている村立て許可時間間隔を経過していません。';

  /* 入力エラー */
  const ERROR_INPUT = '入力エラー';
  const ERROR_INPUT_EMPTY      = 'が記入されていない。';
  const ERROR_INPUT_LIMIT      = 'の文字数が長すぎる。';
  const ERROR_INPUT_LIMIT_OVER = 'が 0 以下、または 99 以上である。';
  const ERROR_INPUT_NG_WORD    = 'に入力禁止文字列が含まれている。';
  const ERROR_INPUT_MAX_USER   = '無効な最大人数です。';
  const ERROR_INPUT_PASSWORD   = '有効な GM ログインパスワードが設定されていません。';

  /* リアルタイム制 */
  const ERROR_INPUT_REAL_TIME_HEADER = 'リアルタイム制の昼・夜の時間';
  const ERROR_INPUT_REAL_TIME_EM     = 'を全角で入力している。';
  const ERROR_INPUT_REAL_TIME_NUMBER = 'が数字ではない。';

  /* オプション変更 */
  const ERROR_CHANGE_PLAYING  = 'はプレイ中です。';
  const ERROR_CHANGE_NOT_GM   = '%s・%s以外は変更できません。';
  const ERROR_CHANGE_MAX_USER = '現在の参加人数より少なくできません。';
}
