<?php
/*
  ◆羊飼い (shepherd_patron)
  ○仕様
  ・追加役職：羊
  ・投票人数：人口の 1 / 6
*/
RoleManager::LoadFile('patron');
class Role_shepherd_patron extends Role_patron {
  public $patron_role = 'mind_sheep';

  function GetVoteNightTargetCount() {
    return max(1, floor(DB::$USER->GetUserCount() / 6));
  }
}
