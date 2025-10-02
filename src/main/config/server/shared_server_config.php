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

    'mystia' => ['name' => 'ミスティア鯖',
		 'url' => 'http://www.kuroienogu.net/mystia/',
		 'encode' => 'UTF-8',
		 'separator' => '',
		 'footer' => '',
		 'disable' => false],

    'sanae' => ['name' => '早苗鯖',
		'url' => 'http://alicegame.xsrv.jp/sanae/',
		'encode' => 'UTF-8',
		'separator' => '',
		'footer' => '',
		'disable' => false],

    'suisei' => ['name' => '翠星石鯖',
		 'url' => 'http://alicegame.xsrv.jp/suisei/',
		 'encode' => 'UTF-8',
		 'separator' => '',
		 'footer' => '',
		 'disable' => false],

    'sousei' => ['name' => '蒼星石テスト鯖',
		 'url' => 'http://alicegame.xsrv.jp/sousei/',
		 'encode' => 'UTF-8',
		 'separator' => '',
		 'footer' => '',
		 'disable' => false],

    'shink' => ['name' => '真紅鯖',
		'url' => 'http://alicegame.xsrv.jp/shinku/',
		'encode' => 'UTF-8',
		'separator' => '',
		'footer' => '',
		'disable' => false],

    'hina' => ['name' => '雛苺テスト鯖',
	       'url' => 'http://alicegame.xsrv.jp/hina/',
	       'encode' => 'UTF-8',
	       'separator' => '',
	       'footer' => '',
	       'disable' => false],

    'bourbonhouse' => ['name' => 'バーボンハウス鯖',
		       'url' => 'http://bourbonhouse.xsrv.jp/jinro/',
		       'encode' => 'UTF-8',
		       'separator' => '',
		       'footer' => '',
		       'disable' => false]
  ];
}
