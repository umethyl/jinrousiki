<?php
/*
  ◆波乱万丈 (random_luck)
  ○仕様
  ・得票数：-2 ～ +2
*/
RoleManager::LoadFile('upper_luck');
class Role_random_luck extends Role_upper_luck {
  public function GetVotePollCount() {
    return Lottery::GetRange(-2, 2);
  }
}
