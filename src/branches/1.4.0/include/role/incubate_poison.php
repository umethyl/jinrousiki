<?php
/*
  ◆潜毒者
  ○仕様
  ・毒：人狼系 + 妖狐陣営 (5日目以降)
*/
class Role_incubate_poison extends Role{
  function Role_incubate_poison(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterPoisonTarget(&$list){
    global $USERS;

    $stack = array();
    foreach($list as $uname){
      if($USERS->ByRealUname($uname)->IsRoleGroup('wolf', 'fox')) $stack[] = $uname;
    }
    $list = $stack;
  }
}
