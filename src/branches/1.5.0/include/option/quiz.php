<?php
/*
  ◆クイズ村 (quiz)
  ○仕様
  ・配役：解答者付加 (出題者以外)
*/
class Option_quiz extends Option{
  function __construct(){ parent::__construct(); }

  function Cast(&$list, &$rand){
    global $ROLES;

    $role = 'panelist';
    foreach(array_keys($list) as $id){
      if($ROLES->stack->uname_list[$id] != 'dummy_boy')  $list[$id] .= ' ' . $role;
    }
    return array($role);
  }
}
