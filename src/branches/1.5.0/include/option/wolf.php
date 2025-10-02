<?php
/*
  ◆人狼追加 (wolf)
  ○仕様
  ・配役：村人 → 人狼
*/
class Option_wolf extends Option{
  function __construct(){ parent::__construct(); }

  function SetRole(&$list, $count){
    global $CAST_CONF;
    if($count >= $CAST_CONF->{$this->name} && $list['human'] > 0){
      $list['human']--;
      $list[$this->name]++;
    }
  }
}
