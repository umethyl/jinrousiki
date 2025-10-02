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
  const SUCCESS    = '編集完了';
  const LOCK       = '[ロック失敗] ';
  const UPDATE     = '[更新失敗] ';
  const NOT_EXISTS = '無効なアイコン番号です：%s';
  const DUPLICATE  = 'アイコン名 "%s" は既に登録されています';
  const USING      = '募集中・プレイ中の村で使用されているアイコンは編集できません';
  const NO_CHANGE  = '変更内容はありません';
}
