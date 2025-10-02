<?php
/*
  ◆無口 (silent)
  ○仕様
  ・自分の発言が一定文字数を超えたらそれ以降が消える
  ・文字数制限は GameConfig->silent_length で定義
  ・ゲームプレイ中で生存時のみ有効 (呼び出し関数側で対応)
*/
class Role_silent extends Role{
  function Role_silent(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSay(&$sentence){
    global $GAME_CONF;
    if(mb_strlen($sentence) > $GAME_CONF->silent_length){
      $sentence = mb_substr($sentence, 0, $GAME_CONF->silent_length) . '……';
    }
  }
}
