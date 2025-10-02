<?php
/*
  ◆暴君 (critical_common)
  ○仕様
  ・投票数：+1
  ・得票数：+100 (5%)
*/
RoleManager::LoadFile('common');
class Role_critical_common extends Role_common {
  function FilterVoteDo(&$count) { $count++; }

  function FilterVotePoll(&$count) {
    if (Lottery::Percent(5)) $count += 100;
  }
}
