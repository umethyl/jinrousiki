<?php
/*
  ◆ひんな神 (critical_patron)
  ○仕様
  ・追加役職：ひんな持ち
  ・得票数：+100 (5%)
*/
RoleManager::LoadFile('patron');
class Role_critical_patron extends Role_patron {
  public $mix_in = array('critical_luck');
  public $patron_role = 'occupied_luck';

  public function IgnoreFilterVotePoll() {
    return ! Lottery::Percent(5);
  }
}
