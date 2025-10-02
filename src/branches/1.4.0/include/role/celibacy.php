<?php
/*
  ◆独身貴族 (celibacy)
  ○仕様
  ・恋人に投票されたらショック死する
*/
class Role_celibacy extends Role{
  function Role_celibacy(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSuddenDeath(&$reason){
    global $ROLES, $USERS;

    if($reason != '') return;
    foreach(array_keys($ROLES->stack->target, $ROLES->actor->uname) as $uname){
      if($USERS->ByRealUname($uname)->IsLovers()){
	$reason = 'CELIBACY';
	break;
      }
    }
  }
}
