<?php
/*
  ◆埋毒者登場 (poison)
  ○仕様
  ・配役：村人2 → 埋毒者1・人狼1
*/
class Option_poison extends Option{
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
