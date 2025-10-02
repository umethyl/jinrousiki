<?php
/*
  ◆聖女 (saint)
  ○仕様
  ・処刑投票が拮抗したら候補者の内訳によって処刑者が変化する
*/
class Role_saint extends RoleVoteAbility{
  function Role_saint(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function DecideVoteKill(&$uname){
    global $ROLES, $USERS;

    if(parent::DecideVoteKill($uname)) return true;
    $stack = array();
    $target_stack = array();
    foreach($ROLES->stack->vote_possible as $target_uname){//最多得票者の情報を収集
      $user = $USERS->ByRealUname($target_uname); //$target_uname は仮想ユーザ
      if($user->IsRole('saint')) $stack[] = $target_uname;
      if($user->GetCamp(true) != 'human') $target_stack[] = $target_uname;
    }
    if(count($stack) > 0 && count($target_stack) < 2){ //対象を一人に固定できる時のみ有効
      if(isset($target_stack[0])) $uname = $target_stack[0];
      elseif(count($stack) == 1)  $uname = $stack[0];
    }
  }
}
