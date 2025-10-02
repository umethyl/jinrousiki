<?php
/*
  ◆騎士 (poison_guard)
  ○仕様
  ・護衛制限：なし
  ・毒：夜限定
*/
RoleLoader::LoadFile('guard');
class Role_poison_guard extends Role_guard {
  public function IsPoison() {
    return DB::$ROOM->IsNight();
  }

  public function UnlimitedGuard() {
    return true;
  }
}
