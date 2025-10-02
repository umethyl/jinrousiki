<?php
/*
  ◆気分屋 (random_voter)
  ○仕様
  ・投票数が -1〜+1 の範囲でランダムに補正がかかる
*/
class Role_random_voter extends Role{
  function Role_random_voter(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoteDo(&$vote_number){
    $vote_number += mt_rand(0, 2) - 1;
  }
}
