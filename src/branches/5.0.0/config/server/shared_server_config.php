<?php
//-- 村情報共有サーバの設定 --//
class SharedServerConfig {
  const DISABLE = false; //無効設定 <表示を [true:無効 / false:有効] にする>

  //表示する他のサーバのリスト
  static public $server_list = [
    'cirno' => ['name' => '真・チルノ鯖',
		'url' => 'https://jinrousiki.osdn.jp/cirno/',
		'encode' => 'UTF-8',
		'separator' => '<!-- atpages banner tag -->',
		'footer' => '</a><br>',
		'disable' => false],
  ];
}
