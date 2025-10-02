<?php
/*
  ◆天邪鬼 (perverseness)
  ○仕様
  ・自分の投票先に複数の人が投票していたらショック死する
*/
class Role_perverseness extends Role{
  function Role_perverseness(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterSuddenDeath(&$reason){
    global $ROLES;
    if($reason == '' && $ROLES->stack->count[$ROLES->stack->target[$ROLES->actor->uname]] > 1){
      $reason = 'PERVERSENESS';
    }
  }
}
