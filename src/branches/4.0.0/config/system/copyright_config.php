<?php
//素材情報設定
class CopyrightConfig {
  //システム標準情報
  public static $list = [
    'システム' => ['PHP4 + MYSQLスクリプト' => 'http://f45.aaa.livedoor.jp/~netfilms/',
		   'mbstringエミュレータ' => 'http://sourceforge.jp/projects/mbemulator/',
		   'Twitter投稿モジュール' => 'https://github.com/abraham/twitteroauth'],
    '写真素材' => ['天の欠片' => 'http://photozou.jp/photo/list/2066445/5445429'],
    'フォント素材' => ['あずきフォント' => 'http://azukifont.mints.ne.jp/']
  ];

  //追加情報
  public static $add_list = [
    '写真素材' => ['Le moineau - すずめのおやど -' => 'http://moineau.fc2web.com/'],
    /*
    'アイコン素材' => ['夏蛍' => 'http://natuhotaru.yukihotaru.com/',
                       'ジギザギのさいはて' => 'http://jigizagi.s57.xrea.com/']
    */
  ];
}
