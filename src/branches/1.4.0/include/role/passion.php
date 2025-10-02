<?php
/*
  ◆恋色迷彩 (passion)
  ○仕様
  ・自分の発言の一部が「恋色」な言葉に変換される
  ・変換テーブルは GameConfig->passion_replace_list で定義する
  ・ゲームプレイ中で生存時のみ有効 (呼び出し関数側で対応)
*/
class Role_passion extends Role{
  function Role_passion(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSay(&$sentence){
    global $GAME_CONF;
    $sentence = strtr($sentence, $GAME_CONF->passion_replace_list);
  }
}
