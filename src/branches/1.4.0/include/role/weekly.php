<?php
/*
  ◆七曜迷彩 (weekly)
  ○仕様
  ・自分の発言の一部が曜日の順番に従って循環変換される
  ・変換テーブルは GameConfig->weekly_replace_list で定義する
  ・ゲームプレイ中で生存時のみ有効 (呼び出し関数側で対応)
*/
class Role_weekly extends Role{
  function Role_weekly(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSay(&$sentence){
    global $GAME_CONF;
    $sentence = strtr($sentence, $GAME_CONF->weekly_replace_list);
  }
}
