<?php
/*
  ◆決定者登場 (decide)
  ○仕様
*/
class Option_decide extends Option{
  function __construct(){ parent::__construct(); }

  function Cast(&$list, &$rand){
    global $CAST_CONF, $ROLES;
    if($ROLES->stack->user_count >= $CAST_CONF->{$this->name}) return $this->CastOnce($list, $rand);
  }
}
