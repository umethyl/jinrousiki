<?php
/*
  ◆解答者 (panelist)
  ○仕様
  ・出題者に投票するとショック死する
  ・投票数が 0 で固定される
*/
class Role_panelist extends Role{
  function Role_panelist(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoteDo(&$vote_number){
    $vote_number = 0;
  }

  function FilterSuddenDeath(&$reason){
    global $ROLES, $USERS;
    if($reason == '' &&
       $USERS->ByRealUname($ROLES->stack->target[$ROLES->actor->uname])->IsRole('quiz')){
      $reason = 'PANELIST';
    }
  }
}
