<?php
/*
  ◆暴君 (critical_common)
  ○仕様
  ・投票数：+1
  ・得票数：+100 (5%)
*/
RoleLoader::LoadFile('common');
class Role_critical_common extends Role_common {
  public $mix_in = array('authority', 'critical_luck');

  protected function IgnoreFilterVotePoll() {
    return ! Lottery::Percent(5);
  }
}
