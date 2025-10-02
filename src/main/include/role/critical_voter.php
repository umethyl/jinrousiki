<?php
/*
  ◆会心 (critical_voter)
  ○仕様
  ・5% の確率で投票数が +100 される
*/
class Role_critical_voter extends Role{
  function Role_critical_voter(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoteDo(&$vote_number){
    $vote_number += mt_rand(1, 100) <= 5 ? 100 : 0;
  }
}
