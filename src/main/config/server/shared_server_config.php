<?php
//-- 村情報共有サーバの設定 --//
class SharedServerConfig {
  const DISABLE = false; //無効設定 <表示を [true:無効 / false:有効] にする>

  //表示する他のサーバのリスト
  static public $server_list = array(
    //-- 設定例 --//
    /*
    'cirno' => array('name' => '真・チルノ鯖',
		      'url' => 'http://jinrousiki.sourceforge.jp/cirno/',
		      'encode' => 'UTF-8',
		      'separator' => '',
		      'footer' => '',
		      'disable' => false),

    'mystia' => array('name' => 'ミスティア鯖',
		   'url' => 'http://www.kuroienogu.net/mystia/',
		   'encode' => 'UTF-8',
		   'separator' => '',
		   'footer' => '',
		   'disable' => false),

    'kaguya' => array('name' => '輝夜鯖',
		      'url' => 'http://www42.atpages.jp/houraisankaguya/',
		      'encode' => 'UTF-8',
		      'separator' => '<!-- atpages banner tag -->',
		      'footer' => '</a><br>',
		      'disable' => false),
    */
				     );
}
