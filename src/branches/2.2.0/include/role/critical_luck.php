<?php
/*
  ◆痛恨 (critical_luck)
  ○仕様
  ・得票数：+100 (5% / 天候「烈日」)
*/
class Role_critical_luck extends Role {
  function FilterVotePoll(&$count) {
    if (DB::$ROOM->IsEvent('critical') || Lottery::Percent(5)) $count += 100;
  }
}
