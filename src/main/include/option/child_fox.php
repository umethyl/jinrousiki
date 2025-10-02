<?php
/*
  ◆子狐登場 (child_fox)
  ○仕様
  ・配役：妖狐 → 子狐
*/
class Option_child_fox extends Option{
  function __construct(){ parent::__construct(); }

  function SetRole(&$list, $count){
    global $CAST_CONF;
    if($count >= $CAST_CONF->{$this->name} && $list['fox'] > 0){
      $list['fox']--;
      $list[$this->name]++;
    }
  }
}
