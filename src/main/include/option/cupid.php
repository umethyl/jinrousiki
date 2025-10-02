<?php
/*
  ◆キューピッド登場 (cupid)
  ○仕様
  ・配役：村人 → キューピッド
*/
class Option_cupid extends Option{
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
