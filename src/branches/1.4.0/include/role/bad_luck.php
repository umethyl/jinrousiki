<?php
/*
  ◆不運 (bad_luck)
  ○仕様
  ・処刑投票が拮抗したら自分が処刑される
*/
class Role_bad_luck extends RoleVoteAbility{
  var $data_type = 'self';
  var $decide_type = 'decide';

  function Role_bad_luck(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }
}
