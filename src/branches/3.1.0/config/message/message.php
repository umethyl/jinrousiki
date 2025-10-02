<?php
//-- 基本システムメッセージ --//
class Message {
  /* 共通移動用リンク */
  const BACK = '←戻る';
  const TOP  = '：<a href="./" target="_top">トップページ</a>からログインしなおしてください';
  const JUMP = '切り替わらないなら <a href="%s" target="_top">ここ</a> 。';

  /* 共通 HTML 用 */
  const VIEW_TITLE   = '観戦ページにジャンプ';
  const VIEW_BODY    = '観戦ページに移動します。';
  const CLOSE_WINDOW = 'ウィンドウを閉じる';
  const NO_FRAME     = 'フレーム非対応のブラウザの方は利用できません。';

  /* DB 接続 */
  const ERROR_TITLE      = '[エラー]';
  const DISABLE_DB       = '接続不可設定になっています';
  const DB_ERROR         = 'データベースサーバエラー';
  const DB_ERROR_LOAD    = 'サーバが混雑しています。時間を置いてから再登録をお願いします。';
  const DB_ERROR_CONNECT = 'MySQL サーバ接続失敗';
  const SQL_ERROR        = 'SQLエラー';

  /* エラー */
  const REQUEST_ERROR  = '引数エラー';
  const SESSION_ERROR  = 'セッション認証エラー';
  const DISABLE_ERROR  = '認証エラー';
  const UNUSABLE_ERROR = 'このスクリプトは使用できない設定になっています。';
  const INVALID_ROOM   = '無効な村番地です';

  /* 区切り文字 */
  const COLON = '：';
  const RANGE = '～';

  /* ユーザ情報 */
  const SYMBOL = '◆';
  const SPACER = '　'; //全角空白

  /* トリップ */
  const TRIP         = '◆';
  const TRIP_CONVERT = '◇';
  const TRIP_KEY     = '＃'; //全角
  const DISABLE_TRIP = 'トリップは使用不可です。';
  const TRIP_FORMAT  = '"%s" 又は "%s" の文字も使用不可です。';
  const TRIP_ERROR   = '村人登録 [入力エラー]';

  /* システムユーザ */
  const SYSTEM    = 'システム';
  const DUMMY_BOY = '身代わり君';
  const GM        = 'GM';

  /* 身代わり君 */
  const DUMMY_BOY_PROFILE    = '僕はおいしくないよ'; //プロフィールコメント
  const DUMMY_BOY_LAST_WORDS = '僕はおいしくないって言ったのに……'; //遺言

  /* 時間 */
  const HOUR   = '時間';
  const MINUTE = '分';
  const SECOND = '秒';

  /* 性別 */
  const MALE   = '男性';
  const FEMALE = '女性';

  /* フォーム */
  const FORM_ALL     = '全て';
  const FORM_EXECUTE = ' 実 行 ';

  /* ログ */
  const LOG_NORMAL         = '正';
  const LOG_REVERSE        = '逆';
  const LOG_DEAD           = '霊';
  const LOG_DEAD_REVERSE   = '逆&amp;霊';
  const LOG_HEAVEN         = '逝';
  const LOG_HEAVEN_REVERSE = '逆&amp;逝';
  const LOG_WATCH          = '観';
  const LOG_WATCH_REVERSE  = '逆&amp;観';
  const LOG_WOLF           = '正&amp;狼';
  const LOG_WOLF_REVERSE   = '逆&amp;狼';

  /* ランダムメッセージ挿入 */
  //GameConfig::RANDOM_MESSAGE を true にすると、この配列の中身がランダムに表示される
  public static $random_message_list = array();
}
