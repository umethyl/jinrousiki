<?php
/*
  ◆幸運 (good_luck)
  ○仕様
  ・処刑投票が拮抗したら自分が候補から除外される
*/
class Role_good_luck extends RoleVoteAbility{
  var $data_type = 'self';
  var $decide_type = 'escape';

  function Role_good_luck(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }
}
