<?php
//サーバのURL
$site_root = 'http://localhost/jinro/';

//サーバのコメント
$server_comment = '';

//データベースサーバのホスト名 hostname:port
//ポート番号を省略するとデフォルトポートがセットされます。(MySQL:3306)
$db_host = 'localhost';

//データベースのユーザ名
$db_uname = 'xxxxxx';

//データベースサーバのパスワード
$db_pass = 'xxxxxx';

//データベース名
$db_name = 'jinrou';

//管理者用パスワード
$system_password = 'xxxxxx';

//ソースアップロードフォームのパスワード
$src_upload_password = 'upload';

//戻り先のページ
$back_page = '';

//デバッグモードのオン/オフ
$DEBUG_MODE = false;

//時差 (秒数)
$OFFSET_SECONDS = 32400; //9時間

//外部ファイルの読み込み
require_once(dirname(__FILE__) . '/config.php');          //高度な設定
require_once(dirname(__FILE__) . '/version.php');         //バージョン情報
require_once(dirname(__FILE__) . '/contenttype_set.php'); //ヘッダの文字コード設定
require_once(dirname(__FILE__) . '/functions.php');       //基本関数
require_once(dirname(__FILE__) . '/../paparazzi.php');    //デバッグ用
if(FindDangerValue($_REQUEST) || FindDangerValue($_SERVER)) die;
?>
