<?php
/*
  ◆鈴蘭人形
  ○仕様
  ・毒：上海人形系以外 (人形遣いは毒対象)
*/
class Role_poison_doll extends Role{
  function Role_poison_doll(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterPoisonTarget(&$list){
    global $USERS;

    $stack = array();
    foreach($list as $uname){
      if(! $USERS->ByRealUname($uname)->IsDoll()) $stack[] = $uname;
    }
    $list = $stack;
  }
}
