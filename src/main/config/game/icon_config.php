<?php
//ゲームプレイ時のアイコン表示設定
class IconConfig {
  const PATH   = 'user_icon';	//ユーザアイコンのパス
  const WIDTH  = 45;	//表示サイズ(幅)
  const HEIGHT = 45;	//表示サイズ(高さ)
  const VIEW   = 100;	//一画面に表示するアイコンの数
  const PAGE   = 10;	//一画面に表示するページ数の数

  static public $dead = 'grave.jpg';	//死者
  static public $wolf = 'wolf.gif';	//狼
}
