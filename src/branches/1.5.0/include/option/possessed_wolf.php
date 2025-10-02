<?php
/*
  ◆憑狼登場 (possessed_wolf)
  ○仕様
  ・配役：人狼 → 憑狼
*/
class Option_possessed_wolf extends Option{
  function __construct(){ parent::__construct(); }

  function SetRole(&$list, $count){
    global $CAST_CONF;
    if($count >= $CAST_CONF->{$this->name} && $list['wolf'] > 0){
      $list['wolf']--;
      $list[$this->name]++;
    }
  }
}
