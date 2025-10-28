<?php
/*
  ◆不人気 (disfavor)
  ○仕様
  ・得票数：+1
*/
RoleLoader::LoadFile('upper_luck');
class Role_disfavor extends Role_upper_luck {
  protected function GetVotePollCount() {
    return 1;
  }
}
