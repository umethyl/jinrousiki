<?php
/*
  ◆痛恨 (critical_luck)
  ○仕様
  ・得票数：+100 (5% / 天候「烈日」)
*/
class Role_critical_luck extends Role {
  function FilterVotePoll(&$number) {
    if (DB::$ROOM->IsEvent('critical') || mt_rand(0, 99) < 5) $number += 100;
  }
}
