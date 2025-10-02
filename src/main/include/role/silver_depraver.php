<?php
/*
  ◆頭人 (silver_depraver)
  ○仕様
  ・仲間表示：なし
*/
RoleManager::LoadFile('depraver');
class Role_silver_depraver extends Role_depraver {
  protected function OutputPartner() { return; }
}
