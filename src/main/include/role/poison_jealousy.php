<?php
/*
  ◆毒橋姫
  ○仕様
  ・毒：恋人
*/
class Role_poison_jealousy extends Role{
  function Role_poison_jealousy(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterPoisonTarget(&$list){
    global $USERS;

    $stack = array();
    foreach($list as $uname){
      if($USERS->ByRealUname($uname)->IsLovers()) $stack[] = $uname;
    }
    $list = $stack;
  }
}
