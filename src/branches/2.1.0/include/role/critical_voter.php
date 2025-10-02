<?php
/*
  ◆会心 (critical_voter)
  ○仕様
  ・投票数：+100 (5% / 天候「烈日」)
*/
class Role_critical_voter extends Role {
  function FilterVoteDo(&$number) {
    if (DB::$ROOM->IsEvent('critical') || mt_rand(0, 99) < 5) $number += 100;
  }
}
