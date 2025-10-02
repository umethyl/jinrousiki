<?php
/*
  ◆波乱万丈 (random_luck)
  ○仕様
  ・得票数：-2 ～ +2
*/
class Role_random_luck extends Role {
  function FilterVotePoll(&$count) {
    $count += Lottery::GetRange(-2, 2);
  }
}
