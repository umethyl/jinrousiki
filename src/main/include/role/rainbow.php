<?php
/*
  ◆虹色迷彩 (rainbow)
  ○仕様
  ・自分の発言の一部が虹の色の順番に従って循環変換される
  ・変換テーブルは GameConfig->rainbow_replace_list で定義する
  ・ゲームプレイ中で生存時のみ有効 (呼び出し関数側で対応)
*/
class Role_rainbow extends Role{
  function Role_rainbow(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSay(&$sentence){
    global $GAME_CONF;
    $sentence = strtr($sentence, $GAME_CONF->rainbow_replace_list);
  }
}
