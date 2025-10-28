<?php
/*
  ◆夢司祭 (dummy_priest)
  ○仕様
  ・役職表示：司祭
  ・司祭：夢系 + 妖精系
*/
RoleLoader::LoadFile('priest');
class Role_dummy_priest extends Role_priest {
  public $display_role = 'priest';

  protected function IgnoreSetPriestEvent() {
    return DB::$ROOM->IsEvent('no_dream');
  }

  protected function GetPriestType() {
    return 'dream';
  }
}
