<?php
//-- エラー表示設定 --//
define('JINROU_DISPLAY_ERROR', false); //デバッグ用
if (JINROU_DISPLAY_ERROR) {
  ini_set('display_errors', 'On');
  error_reporting(E_ALL);
}

//-- 定数を定義 --//
/*
  ServerConfig::SITE_ROOT を使って CSS や画像等をロードする仕様にすると
  ローカルに保存する場合や、ログを別のサーバに移す場合に手間がかかるので
  JINROU_ROOT で相対パスを定義して共通で使用する仕様に変更しました。
  絶対パスが返る dirname() を使ったパスの定義を行わないで下さい。
*/
define('JINROU_CONF', JINROU_ROOT . '/config');
define('JINROU_INC',  JINROU_ROOT . '/include');
define('JINROU_CSS',  JINROU_ROOT . '/css');
define('JINROU_IMG',  JINROU_ROOT . '/img');
define('JINROU_MOD',  JINROU_ROOT . '/module');

//-- 初期化処理 --//
require_once(JINROU_INC . '/loader_class.php');
spl_autoload_register(['Loader', 'AutoLoad']);
Loader::Initialize();
