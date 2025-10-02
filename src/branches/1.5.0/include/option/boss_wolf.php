<?php
/*
  ◆白狼登場 (boss_wolf)
  ○仕様
  ・配役：人狼 → 白狼
*/
class Option_boss_wolf extends Option{
  function __construct(){ parent::__construct(); }

  function SetRole(&$list, $count){
    global $CAST_CONF;
    if($count >= $CAST_CONF->{$this->name} && $list['wolf'] > 0){
      $list['wolf']--;
      $list[$this->name]++;
    }
  }
}
