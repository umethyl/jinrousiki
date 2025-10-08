<?php
//-- UserManager 専用メッセージ --//
class UserManagerMessage {
  /* タイトル */
  const TITLE = '[村人登録]';

  /* 入村画面タイトル */
  const ENTRY_TITLE = '申請書';
  const ENTRY_ROOM  = 'への住民登録を申請します';

  /* 入力フォーム (ユーザ名) */
  const UNAME = 'ユーザ名';
  const UNAME_EXPLAIN_HEADER = '普段は表示されず、他のユーザ名がわかるのは';
  const UNAME_EXPLAIN_FOOTER = '死亡したときとゲーム終了後のみです';
  const TRIP                 = 'の右側はトリップ専用入力欄です';
  const DISABLE_TRIP         = 'トリップ使用不可';
  const NECESSARY_NAME       = '必ずユーザ名を入力してください';
  const NECESSARY_TRIP       = '必ずトリップを入力してください';
  const NECESSARY_NAME_TRIP  = '必ずユーザ名・トリップの両方を入力してください';

  /* 入力フォーム (HN) */
  const HANDLE_NAME = '村人の名前';
  const HANDLE_NAME_EXPLAIN = '村で表示される名前です';

  /* 入力フォーム (パスワード) */
  const PASSWORD = 'パスワード';
  const PASSWORD_EXPLAIN = 'セッションが切れた場合のログイン時に使います';
  const PASSWORD_CAUTION = '暗号化されていないので要注意';

  /* 入力フォーム (性別) */
  const SEX = '性別';
  const SEX_EXPLAIN = '特に意味は無いかも……';

  /* 入力フォーム (プロフィール) */
  const PROFILE = 'プロフィール';
  const PROFILE_EXPLAIN = 'アイコンのポップアップに表示されます<br>未記入でもOKです';

  /* 入力フォーム (役割希望) */
  const WISH_ROLE = '役割希望';
  const WISH_ROLE_ALT  = '←';
  const WISH_ROLE_NONE = '無し';

  /* 入力フォーム (アイコン) */
  const ICON = 'アイコン';
  const ICON_NUMBER      = 'アイコン番号';
  const ICON_EXPLAIN     = 'あなたのアイコンを選択して下さい';
  const ICON_FIX         = '手入力';
  const ICON_FIX_EXPLAIN = '半角英数で入力してください';

  /* 入力フォーム (申請ボタン) */
  const SUBMIT = '村人登録申請';
  const SUBMIT_EXPLAIN = 'ユーザ名、村人の名前、パスワードの前後の空白および改行コードは自動で削除されます';

  /* 入村メッセージ */
  const ENTRY = '%d 番目の村人登録完了、村の寄り合いページに飛びます。';

  /* 登録情報変更 */
  const CHANGE = '登録情報変更';
  const CHANGE_HEADER  = '%s さんが登録情報を変更しました。';
  const CHANGE_NAME    = '村人の名前：%s → %s';
  const CHANGE_ICON    = 'アイコン：No. %d (%s) → No. %d (%s)';
  const CHANGE_NONE    = '変更点はありません。';
  const CHANGE_SUCCESS = '登録情報を変更しました。';

  /* エラー表示 */
  const ERROR = '村人登録 [%s]';

  /* 入村制限 */
  const BLACK_LIST_TITLE = '入村制限';
  const BLACK_LIST = '入村制限ホストです。';

  /* 入力エラー */
  const ERROR_INPUT = '入力エラー';
  const ERROR_INPUT_FOOTER = '別の名前にしてください。';
  const ERROR_INPUT_TEXT   = 'が空です (空白と改行コードは自動で削除されます)。';
  const ERROR_INPUT_EMPTY  = 'が入力されていません。';
  const ERROR_INPUT_UNAME  = 'ユーザ名がありません (トリップのみは不可)。';
  const ERROR_INPUT_TRIP   = 'トリップがありません。';
  const ERROR_INPUT_KICK   = 'キックされた人と同じユーザ名は使用できません (村人名は可)。';

  /* 入力文字数制限 */
  const ERROR_TEXT_LIMIT = '%sは%d文字まで。';

  /* 例外チェック */
  const CHECK_UNAME       = 'ユーザ名「%s」は使用できません。';
  const CHECK_HANDLE_NAME = '村人名「%s」は使用できません。';
  const CHECK_SEX         = '無効な性別です。';
  const CHECK_WISH_ROLE   = '無効な役割希望です。';
  const CHECK_ICON        = '無効なアイコン番号です。';

  /* 入村エラー */
  const LOGIN = '入村不可';
  const CLOSING    = '現在募集停止中です。';
  const PLAYING    = 'すでにゲームが開始されています。';
  const MAX_USER   = '村が満員です。';
  const NOT_EXISTS = '%d 番地の村は存在しません。';
  const FINISHED   = 'すでに村が終了しています。';

  /* 多重登録エラー */
  const DUPLICATE = '多重登録エラー';
  const DUPLICATE_IP   = '同一 IP で多重登録はできません。';
  const DUPLICATE_NAME = 'ユーザ名、または村人名が既に登録してあります。';

  /* セッションエラー */
  const SESSION = 'セッション ID が一致しません。';
}
