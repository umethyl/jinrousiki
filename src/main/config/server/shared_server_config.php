<?php
//-- 村情報共有サーバの設定 --//
final class SharedServerConfig {
  const DISABLE = false; //無効設定 <表示を [true:無効 / false:有効] にする>

  //表示する他のサーバのリスト
  static public $server_list = [
    /*
    '名前' => ['name' => 'サーバ名',
		'url' => 'URL',
		'encode' => 'UTF-8',
		'separator' => '',
		'footer' => '',
		'disable' => false]
    */
  ];
}
