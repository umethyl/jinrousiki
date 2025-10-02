<?php
//-- Login 専用メッセージ --//
class LoginMessage {
  /* 自動ログイン */
  const AUTO_TITLE = '自動ログイン';
  const AUTO_BODY  = 'ログインしています。';

  /* 手動ログイン */
  const MANUALLY_TITLE = 'ログイン';
  const MANUALLY_BODY  = 'ログインしました。';

  /* 手動ログイン失敗 */
  const FAILED_TITLE   = 'ログイン失敗';
  const FAILED_BODY    = 'ユーザ名とパスワードが一致しません。';
  const FAILED_CAUTION = '(空白と改行コードは登録時に自動で削除されている事に注意してください)';
}
