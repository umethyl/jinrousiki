<?php
/*
  ◆老兵 (elder_guard)
  ○仕様
  ・護衛失敗：30% / 制限なし
  ・狩り：なし
  ・投票数：+1
*/
RoleLoader::LoadFile('guard');
class Role_elder_guard extends Role_guard {
  public $mix_in = ['authority'];

  public function IgnoreGuard(User $user) {
    return Lottery::Percent(30) ? true : null;
  }

  public function IgnoreHunt() {
    return true;
  }
}
