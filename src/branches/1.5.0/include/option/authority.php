<?php
/*
  ◆権力者登場 (authority)
  ○仕様
*/
class Option_authority extends Option{
  function __construct(){ parent::__construct(); }

  function Cast(&$list, &$rand){
    global $CAST_CONF, $ROLES;
    if($ROLES->stack->user_count >= $CAST_CONF->{$this->name}) return $this->CastOnce($list, $rand);
  }
}
