<?php
/*
  ◆毒狼登場 (poison_wolf)
  ○仕様
  ・配役：人狼 → 毒狼 / 村人 → 薬師
*/
class Option_poison_wolf extends Option{
  function __construct(){ parent::__construct(); }

  function SetRole(&$list, $count){
    global $CAST_CONF;
    if($count >= $CAST_CONF->{$this->name} && $list['wolf'] > 0 && $list['human'] > 0){
      $list['wolf']--;
      $list[$this->name]++;
      $list['human']--;
      $list['pharmacist']++;
    }
  }
}
