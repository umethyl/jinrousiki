<?php
/*
  ◆自信家 (nervy)
  ○仕様
  ・同一陣営に投票したらショック死する
*/
class Role_nervy extends Role{
  function Role_nervy(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSuddenDeath(&$reason){
    global $ROLES, $USERS;

    if($reason != '') return;
    $target = $USERS->ByRealUname($ROLES->stack->target[$ROLES->actor->uname]);
    if($ROLES->actor->GetCamp(true) == $target->GetCamp(true)) $reason = 'NERVY';
  }
}
