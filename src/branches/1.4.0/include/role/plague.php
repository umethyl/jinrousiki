<?php
/*
  ◆疫病神 (plague)
  ○仕様
  ・処刑投票が拮抗したら自分の投票先が候補から除外される
*/
class Role_plague extends RoleVoteAbility{
  var $data_type = 'target';
  var $decide_type = 'escape';

  function Role_plague(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }
}
