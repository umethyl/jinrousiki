<?php
/*
  ◆無精者 (reduce_voter)
  ○仕様
  ・投票数：-1
*/
class Role_reduce_voter extends Role {
  function FilterVoteDo(&$number) { $number--; }
}
