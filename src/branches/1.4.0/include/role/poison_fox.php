<?php
/*
  ◆管狐
  ○仕様
  ・毒：妖狐陣営以外
*/
class Role_poison_fox extends Role{
  function Role_poison_fox(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterPoisonTarget(&$list){
    global $USERS;

    $stack = array();
    foreach($list as $uname){
      if(! $USERS->ByRealUname($uname)->IsFox()) $stack[] = $uname;
    }
    $list = $stack;
  }
}
