<?php
//-- TOP ページ設定 --//
class TopPageConfig {
  /* 全体設定 */
  const DISABLE_SHARED_SERVER = true; //関連サーバ <表示を [true:無効 / false:有効] にする>

  //表示する他のサーバのリスト (トップページ用)
  public static $server_list = [
    'cirno' => ['name' => '真・チルノ鯖',
		'url' => 'http://jinrousiki.sourceforge.jp/cirno/',
		'encode' => 'UTF-8',
		'separator' => '<!-- atpages banner tag -->',
		'footer' => '</a><br>',
		'disable' => false],

    'mystia' => ['name' => 'ミスティア鯖',
		 'url' => 'http://www.kuroienogu.net/mystia/',
		 'encode' => 'UTF-8',
		 'separator' => '',
		 'footer' => '',
		 'disable' => false]
  ];
}
