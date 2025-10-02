<?php
//-- IconEdit 専用メッセージ --//
class IconEditMessage {
  /* 共通 */
  const TITLE = 'ユーザアイコン編集';

  /* 入力情報チェック */
  const REFERER  = '無効なアクセスです';
  const PASSWORD = 'パスワードが違います';
  const NAME     = 'アイコン名が空欄になっています';

  /* 更新処理 */
  const SUCCESS   = '編集完了';
  const LOCK      = '[ロック失敗] ';
  const UPDATE    = '[更新失敗] ';
  const DUPLICATE = 'アイコン名 "%s" は既に登録されています';
  const NO_CHANGE = '変更内容はありません';
}
