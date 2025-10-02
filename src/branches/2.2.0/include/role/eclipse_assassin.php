<?php
/*
  ◆蝕暗殺者 (eclipse_assassin)
  ○仕様
  ・役職表示：暗殺者
  ・暗殺反射：確率
*/
RoleManager::LoadFile('assassin');
class Role_eclipse_assassin extends Role_assassin {
  public $display_role = 'assassin';

  protected function IsReflectAssassin() {
    return DB::$ROOM->IsEvent('no_reflect_assassin') ? false : Lottery::Percent(30);
  }
}
