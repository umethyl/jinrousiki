<?php
/*
  ◆役者 (actor)
  ○仕様
  ・自分の発言の一部が入れ替わる
  ・ゲームプレイ中で生存時のみ有効 (呼び出し関数側で対応)
*/
class Role_actor extends Role{
  function Role_actor(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSay(&$sentence){
    global $GAME_CONF;
    $sentence = strtr($sentence, $GAME_CONF->actor_replace_list);
  }
}
