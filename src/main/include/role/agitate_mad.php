<?php
/*
  ◆扇動者 (agitate_mad)
  ○仕様
  ・処刑投票が拮抗したら自分の投票先を処刑し、残りをまとめてショック死させる
*/
class Role_agitate_mad extends RoleVoteAbility{
  var $data_type = 'array';

  function Role_agitate_mad(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function DecideVoteKill(&$uname){
    global $ROOM, $ROLES, $USERS;

    if(parent::DecideVoteKill($uname) || ! is_array($ROLES->stack->agitate_mad)) return;
    $stack = array();
    foreach($ROLES->stack->agitate_mad as $actor_uname){ //最多得票者に投票した扇動者の投票先を収集
      $target = $USERS->ByVirtualUname($ROOM->vote[$actor_uname]['target_uname']);
      if(in_array($target->uname, $ROLES->stack->vote_possible)){ //最多得票者リストは仮想ユーザ
	$stack[$target->uname] = true;
      }
    }
    if(count($stack) != 1) return; //対象を一人に固定できる時のみ有効
    $uname = array_shift(array_keys($stack));
    foreach($ROLES->stack->max_voted as $target_uname){
      if($target_uname != $uname){ //$target_uname は仮想ユーザ
	$USERS->SuddenDeath($USERS->ByRealUname($target_uname)->user_no, 'SUDDEN_DEATH_AGITATED');
      }
    }
  }
}
