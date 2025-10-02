<?php
/*
  ◆騎士 (poison_guard)
  ○仕様
  ・護衛失敗：制限なし
  ・毒：夜限定
*/
RoleManager::LoadFile('guard');
class Role_poison_guard extends Role_guard {
  public function IgnoreGuard() {
    return null;
  }

  public function IsPoison() {
    return DB::$ROOM->IsNight();
  }
}
