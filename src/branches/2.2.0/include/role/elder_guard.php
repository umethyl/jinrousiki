<?php
/*
  ◆老兵 (elder_guard)
  ○仕様
  ・護衛失敗：30% / 制限なし
  ・狩り：なし
  ・投票数：+1
*/
RoleManager::LoadFile('guard');
class Role_elder_guard extends Role_guard {
  function IgnoreGuard() { return Lottery::Percent(30) ? true : null; }

  protected function IsHunt(User $user) { return false; }

  function FilterVoteDo(&$count) { $count++; }
}
