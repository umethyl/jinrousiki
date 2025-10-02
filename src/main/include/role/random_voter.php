<?php
/*
  ◆気分屋 (random_voter)
  ○仕様
  ・投票数：-1 ～ +1
*/
class Role_random_voter extends Role {
  function FilterVoteDo(&$count) {
    $count += Lottery::GetRange(-1, 1);
  }
}
