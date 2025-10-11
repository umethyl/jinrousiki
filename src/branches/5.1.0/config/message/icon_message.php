<?php
//-- 基本アイコンメッセージ --//
class IconMessage {
  /* タイトル・リンク */
  const TITLE  = 'ユーザアイコン一覧';
  const TOP    = '←TOP';
  const BACK   = '←アイコン一覧に戻る';
  const VIEW   = 'アイコン一覧';
  const EDIT   = 'アイコン設定の変更';
  const UPLOAD = 'アイコン登録';

  /* 検索カテゴリ */
  const CATEGORY   = 'カテゴリ';
  const APPEARANCE = '出典';
  const AUTHOR     = 'アイコン作者';
  const KEYWORD    = 'キーワード';

  /* 検索フォーム */
  const SORT_BY_NAME   = '名前順に並べ替える';
  const KEYWORD_INPUT  = 'キーワード：';
  const SEARCH         = '検索';
  const SEARCH_EXPLAIN = 'アイコンをクリックすると編集できます (要パスワード)';
  const MULTI_EDIT     = '一括編集';

  /* カテゴリ表示欄 */
  const NOTHING = 'データ無し';
  const SPACE   = '空欄';

  /* フォーム */
  const NUMBER_FORMAT = 'アイコン番号はカンマ区切りで入力してください';
  const NUMBER        = 'アイコン番号';
  const NAME          = 'アイコンの名前';
  const COLOR         = 'アイコン枠の色';
  const EXAMPLE       = '例：#6699CC';
  const DISABLE       = '非表示';
  const PASSWORD      = '編集パスワード';
  const SUBMIT        = '変更';

  /* エラー処理 */
  const LENGTH_LIMIT  = '半角で%d文字、全角で%d文字まで';
  const FILE_LIMIT    = '%sByte まで';
  const SIZE_LIMIT    = '幅%dピクセル × 高さ%dピクセルまで';
  const INVALID_COLOR = '色指定が正しくありません。';
  const COLOR_EXPLAIN = '指定は (例：#6699CC) のように RGB 16進数指定で行ってください。';
  const INPUT_COLOR   = '送信された色指定 → <span class="color">%s</span>';
  const NOT_EXISTS    = '無効なアイコン番号です：%s';
  const USING         = '募集中・プレイ中の村で使用されているアイコンは編集できません';
}
