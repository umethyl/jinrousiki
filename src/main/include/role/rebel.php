<?php
/*
  ◆反逆者 (rebel)
  ○仕様
  ・権力者と同じ人に投票すると権力者 -2 / 反逆者 -1
*/
class Role_rebel extends RoleVoteAbility{
  var $data_type = 'both';

  function Role_rebel(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterRebel(&$message_list, &$count_list){
    global $ROLES;

    //能力発動判定
    $stack = $ROLES->stack;
    if(is_null($stack->authority) || is_null($stack->rebel) ||
       $stack->authority_uname != $stack->rebel_uname) return;

    //権力者 -2 / 反逆者 -1
    $count = 0;
    $list =& $message_list[$stack->authority]['vote_number'];
    $list > 2 ? $count += 2 : $count += $list;
    $list > 2 ? $list  -= 2 : $list = 0;

    $list =& $message_list[$stack->rebel]['vote_number'];
    $list > 1 ? $count++ : $count += $list;
    $list > 1 ? $list--  : $list = 0;

    //投票先の得票数を補正する
    $uname = $stack->rebel_uname;
    $list =& $message_list[$uname]['voted_number'];
    $list > 3 ? $list -= 3 : $list = 0;
    $count_list[$uname] = $list;
  }
}
