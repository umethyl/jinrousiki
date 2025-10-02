<?php
/*
  ◆出題者 (quiz)
  ○仕様
  ・処刑投票が拮抗したら自分の投票先を優先的に処刑する
*/
class Role_quiz extends RoleVoteAbility{
  var $data_type = 'array';

  function Role_quiz(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function DecideVoteKill(&$uname){
    global $ROOM, $ROLES, $USERS;

    if(parent::DecideVoteKill($uname) || ! is_array($ROLES->stack->quiz)) return;
    $stack = array();
    foreach($ROLES->stack->quiz as $actor_uname){ //最多得票者に投票した出題者の投票先を収集
      $target = $USERS->ByVirtualUname($ROOM->vote[$actor_uname]['target_uname']);
      if(in_array($target->uname, $ROLES->stack->vote_possible)){ //最多得票者リストは仮想ユーザ
	$stack[$target->uname] = true;
      }
    }
    //対象を一人に固定できる時のみ有効
    if(count($stack) == 1) $uname = array_shift(array_keys($stack));
  }
}
