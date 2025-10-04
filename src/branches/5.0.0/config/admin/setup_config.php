<?php
//-- 初期セットアップ設定 --//
class SetupConfig {
  //身代わり君アイコン
  public static $dummy_boy_icon = [
    'name'   => '身代わり君用',
    'file'   => '../img/dummy_boy_user_icon.jpg', //IconConfig->path からの相対パス
    'color'  => '#000000',
    'width'  => 45,
    'height' => 45
  ];

  //ユーザアイコンの初期設定
  public static $default_icon = [
     1 => ['name'   => '明灰',
	   'file'   => '001.gif',
	   'color'  => '#DDDDDD',
	   'width'  => 32,
	   'height' => 32],
     2 => ['name'   => '暗灰',
	   'file'   => '002.gif',
	   'color'  => '#999999',
	   'width'  => 32,
	   'height' => 32],
     3 => ['name'   => '黄色',
	   'file'   => '003.gif',
	   'color'  => '#FFD700',
	   'width'  => 32,
	   'height' => 32],
     4 => ['name'   => 'オレンジ',
	   'file'   => '004.gif',
	   'color'  => '#FF9900',
	   'width'  => 32,
	   'height' => 32],
     5 => ['name'   => '赤',
	   'file'   => '005.gif',
	   'color'  => '#FF0000',
	   'width'  => 32,
	   'height' => 32],
     6 => ['name'   => '水色',
	   'file'   => '006.gif',
	   'color'  => '#99CCFF',
	   'width'  => 32,
	   'height' => 32],
     7 => ['name'   => '青',
	   'file'   => '007.gif',
	   'color'  => '#0066FF',
	   'width'  => 32,
	   'height' => 32],
     8 => ['name'   => '緑',
	   'file'   => '008.gif',
	   'color'  => '#00EE00',
	   'width'  => 32,
	   'height' => 32],
     9 => ['name'   => '紫',
	   'file'   => '009.gif',
	   'color'  => '#CC00CC',
	   'width'  => 32,
	   'height' => 32],
     10 => ['name'   => 'さくら色',
	    'file'   => '010.gif',
	    'color'  => '#FF9999',
	    'width'  => 32,
	    'height' => 32]
  ];
}
