<?php
//-- サーバ設定 --//
class ServerConfig {
  /* サイト設定 */
  #const SITE_ROOT = 'http://localhost/jinrou/';		//サーバのurl
  const SITE_ROOT = 'http://localhost/jinrousiki/2.2/';
  const TITLE     = '汝は人狼なりや？';		//タイトル
  const COMMENT   = '';		//サーバのコメント
  const BACK_PAGE = '';		//戻り先のページ
  const ADMIN     = '';		//管理者 (任意)

  /* 文字コード */
  /*
    変更する場合は全てのファイル自体の文字コードを自前で変更してください
    include/init.php も参照してください
  */
  const ENCODE = 'UTF-8';
  const SET_HEADER_ENCODE = false; //ヘッダ強制指定 (海外サーバ等で文字化けする場合に使用します)

  /* パスワード */
  const PASSWORD = 'xxxxxxxx';	//管理者用パスワード
  const SALT     = 'xxxx';	//パスワード暗号化用 salt

  //村作成パスワード (null 以外を設定しておくと村作成画面にパスワード入力欄が表示されます)
  const ROOM_PASSWORD = null;

  /* モード設定 */
  #const DEBUG_MODE    = false;	//デバッグモード (開発テスト用)
  const DEBUG_MODE    = true;
  const DRY_RUN       = false;	//村作成スキップ (開発テスト用)
  const SECRET_ROOM   = false;	//村情報非表示モード (開発テスト用)
  const DISPLAY_ERROR = false;	//エラー強制表示設定 (開発テスト用)

  //村作成禁止 (true にすると村の作成画面が表示されず、村を作成できなくなります)
  const DISABLE_ESTABLISH = false;

  //村メンテナンス停止 (true にすると村の自動廃村処理などが実行されなくなります)
  const DISABLE_MAINTENANCE = false;

  /* 時間設定 */
  const ADJUST_TIME    = false; //時差調整フラグ (タイムゾーンが設定できない環境用)
  const OFFSET_SECONDS = 32400; //時差 (秒数) (JST/9時間)

  //更新前のスクリプトのリビジョン番号
  /*
    ※ この機能は Ver. 1.4.0 beta1 (revision 152) で実装されました。

    更新前のスクリプトの class ScriptInfo (config/version.php) で
    定義されている $revision を設定することで admin/setup.php で
    行われる処理が最適化されます。

    初めて当スクリプトを設置する場合や、データベースを一度完全消去して
    再設置する場合は 0 を設定して下さい。

    更新前のスクリプトに該当ファイルや変数がない場合や、
    バージョンが分からない場合は 1 を設定してください。

    更新後のリビジョン番号と同じか、それより大きな値を設定すると
    admin/setup.php の処理は常時スキップされます。
  */
  const REVISION = 0;
}
