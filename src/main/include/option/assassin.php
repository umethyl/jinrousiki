<?php
/*
  ◆暗殺者登場 (assassin)
  ○仕様
  ・配役：村人2 → 暗殺者1・人狼1
*/
class Option_assassin extends Option{
  function __construct(){ parent::__construct(); }

  function SetRole(&$list, $count){
    global $CAST_CONF;
    if($count >= $CAST_CONF->{$this->name} && $list['human'] > 1){
      $list['human'] -= 2;
      $list[$this->name]++;
      $list['wolf']++;
    }
  }
}
