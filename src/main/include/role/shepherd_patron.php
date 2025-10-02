<?php
/*
  ◆羊飼い (shepherd_patron)
  ○仕様
  ・投票人数：人口の 1 / 6
  ・追加役職：羊
*/
RoleLoader::LoadFile('patron');
class Role_shepherd_patron extends Role_patron {
  protected function GetVoteNightNeedCount() {
    return max(1, floor(DB::$USER->Count() / 6));
  }

  protected function AddDuelistRole(User $user) {
    $this->AddPatronRole($user, 'mind_sheep');
  }
}
