<?php
/*
  ◆波乱万丈 (random_luck)
  ○仕様
  ・得票数に -2〜+2 の範囲でランダムに補正がかかる
*/
class Role_random_luck extends Role{
  function Role_random_luck(){ $this->__construct(); }
  function __construct(){ parent::__construct(); }

  function FilterVoted(&$voted_number){
    $voted_number += (mt_rand(1, 5) - 3);
  }
}
