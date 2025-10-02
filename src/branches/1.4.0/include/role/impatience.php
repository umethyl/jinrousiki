<?php
/*
  ◆短気 (impatience)
  ○仕様
  ・優先順位が低めの決定者相当
  ・再投票になったらショック死する
*/
class Role_impatience extends RoleVoteAbility{
  var $data_type = 'target';
  var $decide_type = 'decide';

  function Role_impatience(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSuddenDeath(&$reason){
    global $ROLES;
    if($reason == '' && $ROLES->stack->revote) $reason = 'IMPATIENCE';
  }
}
