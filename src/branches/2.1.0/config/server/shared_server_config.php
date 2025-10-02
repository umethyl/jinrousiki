<?php
//-- 村情報共有サーバの設定 --//
class SharedServerConfig {
  const DISABLE = true; //無効設定 <表示を [true:無効 / false:有効] にする>

  //表示する他のサーバのリスト
  static public $server_list = array(
    /* 設定例 */
    /*
    'cirno' => array('name' => 'チルノ鯖',
		     'url' => 'http://www12.atpages.jp/cirno/',
		     'encode' => 'UTF-8',
		     'separator' => '<!-- atpages banner tag -->',
		     'footer' => '</a><br>',
		     'disable' => false),
    */
			      );
}
