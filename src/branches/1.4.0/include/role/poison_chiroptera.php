<?php
/*
  ◆毒蝙蝠
  ○仕様
  ・毒：人狼系 + 妖狐陣営 + 蝙蝠陣営
*/
class Role_poison_chiroptera extends Role{
  function Role_poison_chiroptera(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterPoisonTarget(&$list){
    global $USERS;

    $stack = array();
    foreach($list as $uname){
      if($USERS->ByRealUname($uname)->IsRoleGroup('wolf', 'fox', 'chiroptera', 'fairy')){
	$stack[] = $uname;
      }
    }
    $list = $stack;
  }
}
