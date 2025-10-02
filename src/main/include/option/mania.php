<?php
/*
  ◆神話マニア登場 (mania)
  ○仕様
  ・配役：村人 → 神話マニア
*/
class Option_mania extends Option{
  function __construct(){ parent::__construct(); }

  function SetRole(&$list, $count){
    global $CAST_CONF, $ROOM;
    if($count >= $CAST_CONF->{$this->name} && ! $ROOM->IsOption('full_' . $this->name) &&
       $list['human'] > 0){
      $list['human']--;
      $list[$this->name]++;
    }
  }
}
