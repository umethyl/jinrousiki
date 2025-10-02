<?php
/*
  ◆人気者 (star)
  ○仕様
  ・得票数が -1 される
*/
class Role_star extends Role{
  function Role_star(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoted(&$voted_number){
    $voted_number--;
  }
}
