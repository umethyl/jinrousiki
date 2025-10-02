<?php
/*
  ◆騎士 (poison_guard)
  ○仕様
  ・護衛失敗：制限なし
  ・毒：夜限定
*/
RoleLoader::LoadFile('guard');
class Role_poison_guard extends Role_guard {
  public function IsPoison() {
    return DB::$ROOM->IsNight();
  }

  public function IgnoreGuard(User $user) {
    return null;
  }
}
