<?php
/*
  ◆人気者 (star)
  ○仕様
  ・得票数：-1
*/
RoleManager::LoadFile('upper_luck');
class Role_star extends Role_upper_luck {
  public function GetVotePollCount() {
    return -1;
  }
}
