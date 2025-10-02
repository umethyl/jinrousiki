<?php
//-- IconUpload 専用メッセージ --//
class IconUploadMessage {
  /* 共通 */
  const TITLE   = 'ユーザアイコンアップロード';
  const DISABLE = '現在アップロードは停止しています';

  /* フォーム */
  const LINK        = '→アイコン一覧';
  const CAUTION     = '＊あらかじめ指定する大きさ (%s) にリサイズしてからアップロードしてください。';
  const ICON_FORMAT = 'アイコン指定 (jpg / gif / png 形式で登録して下さい。%s)';
  const FILE_SELECT = 'ファイル選択';
  const SUBMIT      = '登録';
  const FIX_COLOR   = '手入力';

  /* アップロード処理 */
  const COMMAND     = '無効なコマンドです';
  const RETRY       = '再度実行してください。';
  const REFERER     = '無効なアクセスです';
  const FILE_UPLOAD = 'ファイルのアップロードエラーが発生しました。';
  const NAME        = 'アイコン名を入力してください';
  const FILE_EMPTY  = 'ファイルが空です';
  const FILE_SIZE   = 'ファイルサイズは ';
  const FILE_FORMAT = ' : jpg、gif、png 以外のファイルは登録できません';
  const SIZE_LIMIT  = 'アイコンのサイズが不正です (%s)';
  const UPLOAD_SIZE = '送信されたファイル → <span class="color">幅 %d、高さ %d</span>';
  const OVER        = 'これ以上登録できません';
  const DUPLICATE   = 'アイコン名 "%s" は既に登録されています';
  const FILE_COPY   = 'ファイルのコピーに失敗しました。';

  /* 確認ページ */
  const CHECK    = '[確認]';
  const MESSAGE  = 'ファイルをアップロードしました。<br>今だけやりなおしできます';
  const CONFIRM  = 'よろしいですか？';
  const CHECK_OK = '登録完了';
  const CHECK_NG = 'やりなおし';

  /* 完了処理 */
  const SUCCESS        = 'アイコン登録完了';
  const JUMP_VIEW      = '：登録ページに飛びます。';
  const SESSION_DELETE = 'セッションの削除に失敗しました。';

  /* 削除処理 */
  const DELETE      = 'アイコン削除完了';
  const JUMP_UPLOAD = '：登録ページに飛びます。';
  const SESSION     = '削除失敗：アップロードセッションが一致しません';
  const DB_ERROR    = 'サーバが混雑しているため、削除に失敗しました。<br>管理者に問い合わせてください。';
}
