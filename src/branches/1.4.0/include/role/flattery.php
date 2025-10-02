<?php
/*
  ◆ゴマすり (flattery)
  ○仕様
  ・自分の投票先に他の人が投票していなければショック死する
*/
class Role_flattery extends Role{
  function Role_flattery(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSuddenDeath(&$reason){
    global $ROLES;
    if($reason == '' && $ROLES->stack->count[$ROLES->stack->target[$ROLES->actor->uname]] < 2){
      $reason = 'FLATTERY';
    }
  }
}
