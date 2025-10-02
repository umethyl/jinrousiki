<?php
/*
  ◆毒狼
  ○仕様
  ・毒：人狼系以外
*/
class Role_poison_wolf extends Role{
  function Role_poison_wolf(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterPoisonTarget(&$list){
    global $USERS;

    $stack = array();
    foreach($list as $uname){
      if(! $USERS->ByRealUname($uname)->IsWolf()) $stack[] = $uname;
    }
    $list = $stack;
  }
}
