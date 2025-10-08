<?php
/*
  ◆掃除屋 (sweep_assassin)
  ○仕様
  ・投票：キャンセル投票不可
*/
RoleLoader::LoadFile('assassin');
class Role_sweep_assassin extends Role_assassin {
  protected function DisableNotAction() {
    return true;
  }
}
