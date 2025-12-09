<?php
//-- TOP ページ設定 --//
final class TopPageConfig {
  /* 全体設定 */
  const DISABLE_SHARED_SERVER = true; //関連サーバ <表示を [true:無効 / false:有効] にする>

  //表示する他のサーバのリスト (トップページ用)
  public static $server_list = [
     /* 設定例
    'cirno' => ['name' => '真・チルノ鯖',
		'url' => 'http://jinrousiki.sourceforge.jp/cirno/',
		'encode' => 'UTF-8',
		'separator' => '<!-- atpages banner tag -->',
		'footer' => '</a><br>',
		'disable' => false]
     */
  ];
}
