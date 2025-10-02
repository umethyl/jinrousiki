<?php
/*
  ◆夢司祭 (dummy_priest)
  ○仕様
  ・役職表示：司祭
  ・司祭：夢系 + 妖精系
*/
RoleManager::LoadFile('priest');
class Role_dummy_priest extends Role_priest {
  public $display_role = 'priest';
  public $priest_type  = 'dream';

  protected function SetPriest() {
    return ! DB::$ROOM->IsEvent('no_dream') && parent::SetPriest();
  }
}
