<?php
/*
  ◆長老 (elder)
  ○仕様
  ・投票数：+1 (3% で +100)
*/
class Role_elder extends Role {
  function FilterVoteDo(&$count) {
    $count += Lottery::Percent(3) ? 100 : 1;
  }
}
