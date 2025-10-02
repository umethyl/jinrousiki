<?php
/*
  ◆決定者 (decide)
  ○仕様
  ・処刑投票が拮抗したら自分の投票先が処刑される
*/
class Role_decide extends RoleVoteAbility{
  var $data_type = 'target';
  var $decide_type = 'decide';

  function Role_decide(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }
}
