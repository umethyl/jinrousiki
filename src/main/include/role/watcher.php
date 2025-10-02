<?php
/*
  ◆傍観者 (watcher)
  ○仕様
  ・投票数が 0 で固定される
*/
class Role_watcher extends Role{
  function Role_watcher(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoteDo(&$vote_number){
    $vote_number = 0;
  }
}
