<?php
/*
  ◆長老 (elder)
  ○仕様
  ・投票数：+1 (3% で +100)
*/
class Role_elder extends Role {
  function FilterVoteDo(&$number) {
    $number += mt_rand(0, 99) < 3 ? 100 : 1;
  }
}
